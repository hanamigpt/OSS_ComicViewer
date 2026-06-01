<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

$configFile = BASE_PATH . '/config/config.php';
if (!is_file($configFile)) {
    $configFile = BASE_PATH . '/config/config.example.php';
}

$GLOBALS['app_config'] = require $configFile;

date_default_timezone_set($GLOBALS['app_config']['timezone'] ?? 'UTC');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once BASE_PATH . '/src/helpers.php';
require_once BASE_PATH . '/src/db.php';
require_once BASE_PATH . '/src/csrf.php';
require_once BASE_PATH . '/src/auth.php';
require_once BASE_PATH . '/src/repositories.php';
require_once BASE_PATH . '/src/upload.php';
