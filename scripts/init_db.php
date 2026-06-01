<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$schemaPath = BASE_PATH . '/database/schema.sql';
if (!is_file($schemaPath)) {
    fwrite(STDERR, "Missing database/schema.sql\n");
    exit(1);
}

db()->exec((string) file_get_contents($schemaPath));

echo "Database initialized at " . app_config('database_path') . PHP_EOL;
