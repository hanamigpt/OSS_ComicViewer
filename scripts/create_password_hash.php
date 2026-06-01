<?php

declare(strict_types=1);

$password = $argv[1] ?? null;
if ($password === null || $password === '') {
    fwrite(STDERR, "Usage: php scripts/create_password_hash.php \"your-password\"\n");
    exit(1);
}

echo password_hash($password, PASSWORD_DEFAULT) . PHP_EOL;
