<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

if (is_admin_logged_in()) {
    redirect('/admin/');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!login_admin($username, $password)) {
        $errors[] = 'Invalid username or password.';
    } else {
        redirect('/admin/');
    }
}

$siteTitle = (string) app_config('site_title', 'Vertical Scroll Comic CMS');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin login - <?= e($siteTitle) ?></title>
    <link rel="stylesheet" href="/assets/admin.css">
</head>
<body class="login-page">
    <main class="login-card">
        <h1>Admin login</h1>
        <?php render_errors($errors); ?>
        <form method="post">
            <?= csrf_field() ?>
            <label>
                Username
                <input type="text" name="username" autocomplete="username" required autofocus>
            </label>
            <label>
                Password
                <input type="password" name="password" autocomplete="current-password" required>
            </label>
            <button type="submit">Log in</button>
        </form>
    </main>
</body>
</html>
