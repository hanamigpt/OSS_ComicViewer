<?php

declare(strict_types=1);

function is_admin_logged_in(): bool
{
    return !empty($_SESSION['admin_authenticated']);
}

function require_admin(): void
{
    if (!is_admin_logged_in()) {
        redirect('/admin/login.php');
    }
}

function login_admin(string $username, string $password): bool
{
    $expectedUsername = (string) app_config('admin_username', 'admin');
    $passwordHash = (string) app_config('admin_password_hash', '');

    if (!hash_equals($expectedUsername, $username)) {
        return false;
    }

    if (!password_verify($password, $passwordHash)) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['admin_authenticated'] = true;
    $_SESSION['admin_username'] = $username;
    csrf_token();

    return true;
}

function logout_admin(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}
