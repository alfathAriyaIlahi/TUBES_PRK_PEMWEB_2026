<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Jalankan perintah ini dari terminal CLI interaktif.\n");
    exit(1);
}

function read_prompt(string $prompt): string
{
    if (function_exists('readline')) {
        return (string) readline($prompt);
    }
    fwrite(STDOUT, $prompt);
    $value = fgets(STDIN);
    return is_string($value) ? rtrim($value, "\r\n") : '';
}

function read_secret(string $prompt): string
{
    if (PHP_OS_FAMILY === 'Windows') {
        $powerShell = getenv('SystemRoot') . '\\System32\\WindowsPowerShell\\v1.0\\powershell.exe';
        $script = '$secure = Read-Host "' . addslashes($prompt) . '" -AsSecureString; '
            . '$pointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($secure); '
            . 'try { [Runtime.InteropServices.Marshal]::PtrToStringBSTR($pointer) } '
            . 'finally { [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($pointer) }';
        $process = proc_open(
            [$powerShell, '-NoProfile', '-Command', $script],
            [0 => STDIN, 1 => ['pipe', 'w'], 2 => STDERR],
            $pipes,
        );
        if (!is_resource($process)) {
            throw new RuntimeException('Prompt kata sandi tersembunyi tidak dapat dijalankan.');
        }
        $value = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $exitCode = proc_close($process);
        if ($exitCode !== 0 || !is_string($value)) {
            throw new RuntimeException('Kata sandi tidak dapat dibaca.');
        }
        return rtrim($value, "\r\n");
    }

    $terminal = proc_open(['stty', '-echo'], [0 => STDIN, 1 => STDOUT, 2 => STDERR], $pipes);
    if (!is_resource($terminal) || proc_close($terminal) !== 0) {
        throw new RuntimeException('Jalankan perintah ini pada terminal interaktif.');
    }
    fwrite(STDOUT, $prompt);
    try {
        $value = fgets(STDIN);
    } finally {
        proc_close(proc_open(['stty', 'echo'], [0 => STDIN, 1 => STDOUT, 2 => STDERR], $pipes));
        fwrite(STDOUT, "\n");
    }
    if (!is_string($value)) {
        throw new RuntimeException('Kata sandi tidak dapat dibaca.');
    }
    return rtrim($value, "\r\n");
}

try {
    $nim = normalize_nim(read_prompt('NPM admin (4-20 digit): '));
    $fullName = required_text(read_prompt('Nama admin: '), 'Nama admin', 120);
    $password = read_secret('Kata sandi admin (minimal 8 karakter): ');
    $confirmation = read_secret('Ulangi kata sandi: ');

    if (strlen($password) < 8 || strlen($password) > 72) {
        throw new InputError('Kata sandi harus memiliki 8 sampai 72 byte.');
    }
    if (!hash_equals($password, $confirmation)) {
        throw new InputError('Kata sandi tidak cocok.');
    }

    $statement = database()->prepare(
        "INSERT INTO users (nim, full_name, password_hash, role)
         VALUES (:nim, :full_name, :password_hash, 'admin')",
    );
    $statement->execute([
        'nim' => $nim,
        'full_name' => $fullName,
        'password_hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
    ]);
    fwrite(STDOUT, "Akun admin berhasil dibuat. Masuk melalui /index.php.\n");
} catch (Throwable $error) {
    $message = $error instanceof PDOException && $error->getCode() === '23505'
        ? 'NPM tersebut sudah terdaftar.'
        : $error->getMessage();
    fwrite(STDERR, "Admin tidak dapat dibuat: {$message}\n");
    exit(1);
}
