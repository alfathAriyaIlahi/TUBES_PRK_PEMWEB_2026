<?php
declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

try {
    $schema = file_get_contents(APP_ROOT . '/database/schema.sql');
    if ($schema === false) {
        throw new RuntimeException('Skema database tidak dapat dibaca.');
    }
    database()->exec($schema);
    fwrite(STDOUT, "Skema database ADVOKITA berhasil diterapkan.\n");
} catch (Throwable $error) {
    fwrite(STDERR, 'Gagal menyiapkan database: ' . $error->getMessage() . "\n");
    exit(1);
}
