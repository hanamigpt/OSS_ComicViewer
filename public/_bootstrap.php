<?php

declare(strict_types=1);

$envBootstrap = $_SERVER['OSS_COMICVIEWER_BOOTSTRAP'] ?? getenv('OSS_COMICVIEWER_BOOTSTRAP') ?: null;

$bootstrapCandidates = array_filter([
    is_string($envBootstrap) ? $envBootstrap : null,
    __DIR__ . '/../src/bootstrap.php',
    __DIR__ . '/../app/src/bootstrap.php',
]);

foreach ($bootstrapCandidates as $bootstrapFile) {
    if (is_file($bootstrapFile)) {
        require_once $bootstrapFile;
        return;
    }
}

http_response_code(500);
echo 'Application bootstrap file was not found. Check OSS_COMICVIEWER_BOOTSTRAP or the deployment directory layout.';
exit;
