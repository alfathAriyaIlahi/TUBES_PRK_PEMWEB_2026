# Menyiapkan role dan database PostgreSQL lokal untuk ADVOKITA.
$ErrorActionPreference = "Stop"

$projectRoot = Split-Path -Parent $PSScriptRoot
$envPath = Join-Path $projectRoot ".env"
$principal = [Security.Principal.WindowsPrincipal]::new(
  [Security.Principal.WindowsIdentity]::GetCurrent()
)

if (
  -not $principal.IsInRole(
    [Security.Principal.WindowsBuiltInRole]::Administrator
  )
) {
  throw "Buka PowerShell sebagai Administrator lalu jalankan skrip ini lagi."
}

$services = @(
  Get-CimInstance Win32_Service |
    Where-Object {
      $_.Name -like "postgresql-*" -and
      $_.State -eq "Running" -and
      $_.PathName -match '"(?<pgctl>[^"]*pg_ctl\.exe)"\s+runservice.*-D\s+"(?<data>[^"]+)"'
    }
)

if ($services.Count -ne 1) {
  throw "Diperlukan tepat satu service PostgreSQL aktif. Ditemukan: $($services.Count)."
}

$service = $services[0]
$serviceMatch = [regex]::Match(
  $service.PathName,
  '"(?<pgctl>[^"]*pg_ctl\.exe)"\s+runservice.*-D\s+"(?<data>[^"]+)"'
)
$pgctl = $serviceMatch.Groups["pgctl"].Value
$dataDirectory = $serviceMatch.Groups["data"].Value
$psql = Join-Path (Split-Path -Parent $pgctl) "psql.exe"
$configPath = Join-Path $dataDirectory "pg_hba.conf"
$backupPath = "$configPath.advokita-setup-backup"

if (-not (Test-Path $psql) -or -not (Test-Path $configPath)) {
  throw "Executable psql atau konfigurasi pg_hba.conf tidak ditemukan."
}

if (Test-Path $backupPath) {
  Write-Warning "Memulihkan pg_hba.conf dari backup proses setup sebelumnya."
  [IO.File]::WriteAllBytes(
    $configPath,
    [IO.File]::ReadAllBytes($backupPath)
  )
  $reloadOutput = & $pgctl reload -D $dataDirectory 2>&1
  if ($LASTEXITCODE -ne 0) {
    throw "File konfigurasi dipulihkan, tetapi reload gagal. Restart service PostgreSQL sebelum melanjutkan: $($reloadOutput -join [Environment]::NewLine)"
  }
  Remove-Item -LiteralPath $backupPath -Force
}

$config = if (Test-Path $envPath) {
  [IO.File]::ReadAllText($envPath)
} else {
  [IO.File]::ReadAllText((Join-Path $projectRoot ".env.example"))
}

$passwordMatch = [regex]::Match($config, "(?m)^PGPASSWORD=(.*)$")
if (
  $passwordMatch.Success -and
  $passwordMatch.Groups[1].Value -and
  $passwordMatch.Groups[1].Value -ne "CHANGE_THIS_TO_THE_LOCAL_DATABASE_PASSWORD" -and
  $passwordMatch.Groups[1].Value -ne "replace-with-a-local-password"
) {
  throw "PGPASSWORD di .env sudah berisi nilai. Skrip tidak akan menimpa konfigurasi yang sudah ada."
}

foreach ($setting in @(@("PGUSER", "advokita_app"), @("PGDATABASE", "advokita"))) {
  $match = [regex]::Match($config, "(?m)^$($setting[0])=(.*)$")
  if ($match.Success -and $match.Groups[1].Value -ne $setting[1]) {
    throw "$($setting[0]) di .env bukan nilai default proyek. Skrip tidak mengubah database khusus."
  }
}

$port = 5432
$portMatch = [regex]::Match($config, "(?m)^PGPORT=(\d+)\s*$")
if ($portMatch.Success) {
  $port = [int]$portMatch.Groups[1].Value
}

if ($port -lt 1 -or $port -gt 65535) {
  throw "PGPORT di .env tidak valid."
}

function Invoke-Psql {
  param([string[]]$Arguments)

  $output = & $psql @Arguments 2>&1
  if ($LASTEXITCODE -ne 0) {
    throw "Perintah PostgreSQL gagal: $($output -join [Environment]::NewLine)"
  }

  return ($output -join "`n").Trim()
}

function New-RandomHex {
  param([int]$ByteCount)

  $bytes = New-Object byte[] $ByteCount
  $generator = [Security.Cryptography.RandomNumberGenerator]::Create()
  try {
    $generator.GetBytes($bytes)
  } finally {
    $generator.Dispose()
  }

  return [BitConverter]::ToString($bytes).Replace("-", "").ToLowerInvariant()
}

$connectionArguments = @(
  "-X",
  "-w",
  "-h",
  "127.0.0.1",
  "-p",
  "$port",
  "-U",
  "postgres",
  "-d",
  "postgres",
  "-v",
  "ON_ERROR_STOP=1"
)
$originalConfig = [IO.File]::ReadAllBytes($configPath)
$configChanged = $false
$roleCreated = $false
$databaseCreated = $false
$sqlPath = Join-Path $env:TEMP "advokita-setup-$([guid]::NewGuid().ToString('N')).sql"
$operationError = $null
$restoreError = $null
$appPassword = New-RandomHex 32

try {
  [IO.File]::WriteAllBytes($backupPath, $originalConfig)

  $temporaryRule = [Text.Encoding]::ASCII.GetBytes(
    "# Temporary local-only ADVOKITA setup`r`nhost postgres postgres 127.0.0.1/32 trust`r`n"
  )
  $hasUtf8Bom =
    $originalConfig.Length -ge 3 -and
    $originalConfig[0] -eq 239 -and
    $originalConfig[1] -eq 187 -and
    $originalConfig[2] -eq 191
  $prefixLength = if ($hasUtf8Bom) { 3 } else { 0 }
  $temporaryConfig = New-Object byte[] ($originalConfig.Length + $temporaryRule.Length)

  if ($hasUtf8Bom) {
    [Array]::Copy($originalConfig, 0, $temporaryConfig, 0, 3)
  }

  [Array]::Copy(
    $temporaryRule,
    0,
    $temporaryConfig,
    $prefixLength,
    $temporaryRule.Length
  )
  [Array]::Copy(
    $originalConfig,
    $prefixLength,
    $temporaryConfig,
    $prefixLength + $temporaryRule.Length,
    $originalConfig.Length - $prefixLength
  )

  $configChanged = $true
  [IO.File]::WriteAllBytes($configPath, $temporaryConfig)

  $reloadOutput = & $pgctl reload -D $dataDirectory 2>&1
  if ($LASTEXITCODE -ne 0) {
    throw "PostgreSQL gagal memuat aturan akses lokal sementara: $($reloadOutput -join [Environment]::NewLine)"
  }

  Invoke-Psql -Arguments ($connectionArguments + @("-tAc", "SELECT 1")) |
    Out-Null

  $roleCount = Invoke-Psql -Arguments (
    $connectionArguments + @("-tAc", "SELECT COUNT(*) FROM pg_roles WHERE rolname = 'advokita_app'")
  )
  $databaseCount = Invoke-Psql -Arguments (
    $connectionArguments + @("-tAc", "SELECT COUNT(*) FROM pg_database WHERE datname = 'advokita'")
  )

  if ($roleCount -ne "0" -or $databaseCount -ne "0") {
    throw "Role advokita_app atau database advokita sudah ada. Tidak ada data atau kredensial yang diubah."
  }

  [IO.File]::WriteAllText(
    $sqlPath,
    "CREATE ROLE advokita_app LOGIN PASSWORD '$appPassword';",
    [Text.Encoding]::ASCII
  )
  Invoke-Psql -Arguments ($connectionArguments + @("-f", $sqlPath)) |
    Out-Null
  $roleCreated = $true

  Invoke-Psql -Arguments (
    $connectionArguments + @("-c", "CREATE DATABASE advokita OWNER advokita_app;")
  ) | Out-Null
  $databaseCreated = $true

  $sessionMatch = [regex]::Match($config, "(?m)^SESSION_SECRET=(.*)$")
  $sessionSecret = if (
    $sessionMatch.Success -and $sessionMatch.Groups[1].Value.Length -ge 32
  ) {
    $sessionMatch.Groups[1].Value
  } else {
    New-RandomHex 32
  }

  if ($passwordMatch.Success) {
    $config = [regex]::Replace(
      $config,
      "(?m)^PGPASSWORD=.*$",
      "PGPASSWORD=$appPassword"
    )
  } else {
    $config = "$($config.TrimEnd())`r`nPGPASSWORD=$appPassword`r`n"
  }

  if ($sessionMatch.Success) {
    $config = [regex]::Replace(
      $config,
      "(?m)^SESSION_SECRET=.*$",
      "SESSION_SECRET=$sessionSecret"
    )
  } else {
    $config = "$($config.TrimEnd())`r`nSESSION_SECRET=$sessionSecret`r`n"
  }

  [IO.File]::WriteAllText(
    $envPath,
    $config,
    [Text.UTF8Encoding]::new($false)
  )
} catch {
  $operationError = $_.Exception.Message

  if ($roleCreated -and -not $databaseCreated) {
    try {
      $databaseCount = Invoke-Psql -Arguments (
        $connectionArguments + @("-tAc", "SELECT COUNT(*) FROM pg_database WHERE datname = 'advokita'")
      )
      if ($databaseCount -eq "0") {
        Invoke-Psql -Arguments (
          $connectionArguments + @("-c", "DROP ROLE advokita_app;")
        ) | Out-Null
      }
    } catch {
      $operationError += "`nCleanup note: $($_.Exception.Message)"
    }
  }
} finally {
  if ($configChanged) {
    try {
      [IO.File]::WriteAllBytes($configPath, $originalConfig)
      $reloadOutput = & $pgctl reload -D $dataDirectory 2>&1
      if ($LASTEXITCODE -ne 0) {
        throw "pg_ctl reload gagal: $($reloadOutput -join [Environment]::NewLine)"
      }
      Remove-Item -LiteralPath $backupPath -Force
    } catch {
      $restoreError = $_.Exception.Message
    }
  } elseif (Test-Path $backupPath) {
    Remove-Item -LiteralPath $backupPath -Force
  }

  if (Test-Path $sqlPath) {
    Remove-Item -LiteralPath $sqlPath -Force
  }

  Remove-Item Env:PGPASSWORD -ErrorAction SilentlyContinue
}

if ($restoreError) {
  throw "Gagal memulihkan konfigurasi PostgreSQL. Backup asli tersimpan di '$backupPath'. Pulihkan sebelum melanjutkan. Detail: $restoreError"
}

if ($operationError) {
  throw $operationError
}

$env:PGPASSWORD = $appPassword
try {
  Invoke-Psql -Arguments @(
    "-X",
    "-w",
    "-h",
    "127.0.0.1",
    "-p",
    "$port",
    "-U",
    "advokita_app",
    "-d",
    "advokita",
    "-tAc",
    "SELECT 1"
  ) | Out-Null
} finally {
  Remove-Item Env:PGPASSWORD -ErrorAction SilentlyContinue
}

Write-Output "Database dan role ADVOKITA berhasil disiapkan."
Write-Output "Aturan autentikasi PostgreSQL semula telah dipulihkan."
Write-Output "Konfigurasi lokal tersimpan di .env; jangan bagikan atau commit file tersebut."
