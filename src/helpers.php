<?php

declare(strict_types=1);

function app_config(?string $key = null, mixed $default = null): mixed
{
    $config = $GLOBALS['app_config'] ?? [];
    if ($key === null) {
        return $config;
    }

    return $config[$key] ?? $default;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function url_path(string $path, array $params = []): string
{
    $query = http_build_query($params);
    return $query === '' ? $path : $path . '?' . $query;
}

function now(): string
{
    return date('c');
}

function flash(?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'] = $message;
        return null;
    }

    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $value = (string) $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $value;
}

function render_errors(array $errors): void
{
    if ($errors === []) {
        return;
    }

    echo '<div class="alert alert-error"><strong>Please fix the following:</strong><ul>';
    foreach ($errors as $error) {
        echo '<li>' . e($error) . '</li>';
    }
    echo '</ul></div>';
}

function slugify(string $value): string
{
    $value = trim($value);
    $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    if ($converted !== false) {
        $value = $converted;
    }

    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-');
}

function is_valid_slug(string $slug): bool
{
    return (bool) preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug);
}

function selected(mixed $actual, mixed $expected): string
{
    return (string) $actual === (string) $expected ? ' selected' : '';
}

function checked(bool $condition): string
{
    return $condition ? ' checked' : '';
}

function excerpt(?string $text, int $length = 160): string
{
    $text = trim((string) $text);
    if (!function_exists('mb_strlen') || !function_exists('mb_substr')) {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length - 1) . '...';
    }

    if (mb_strlen($text) <= $length) {
        return $text;
    }

    return mb_substr($text, 0, $length - 1) . '...';
}

function asset_url(?string $relativePath): ?string
{
    if ($relativePath === null || $relativePath === '') {
        return null;
    }

    return '/' . ltrim($relativePath, '/');
}

function decode_settings(?string $json): array
{
    $decoded = json_decode($json ?: '{}', true);
    return is_array($decoded) ? $decoded : [];
}

function encode_settings(array $settings): string
{
    return json_encode($settings, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
}

function ini_size_to_bytes(string $value): ?int
{
    $value = trim($value);
    if ($value === '') {
        return null;
    }

    if ($value === '-1') {
        return null;
    }

    $unit = strtolower(substr($value, -1));
    $number = (float) $value;
    $bytes = match ($unit) {
        'g' => $number * 1024 * 1024 * 1024,
        'm' => $number * 1024 * 1024,
        'k' => $number * 1024,
        default => $number,
    };

    return max(0, (int) $bytes);
}

function php_upload_max_bytes(): ?int
{
    return ini_size_to_bytes((string) ini_get('upload_max_filesize'));
}

function php_post_max_bytes(): ?int
{
    return ini_size_to_bytes((string) ini_get('post_max_size'));
}

function effective_upload_max_bytes(): int
{
    $limits = [(int) app_config('upload_max_bytes', 10 * 1024 * 1024)];
    $phpUploadMax = php_upload_max_bytes();
    if ($phpUploadMax !== null && $phpUploadMax > 0) {
        $limits[] = $phpUploadMax;
    }

    return min($limits);
}

function human_bytes(int $bytes): string
{
    if ($bytes >= 1024 * 1024 * 1024) {
        return rtrim(rtrim(number_format($bytes / (1024 * 1024 * 1024), 1), '0'), '.') . ' GB';
    }
    if ($bytes >= 1024 * 1024) {
        return rtrim(rtrim(number_format($bytes / (1024 * 1024), 1), '0'), '.') . ' MB';
    }
    if ($bytes >= 1024) {
        return rtrim(rtrim(number_format($bytes / 1024, 1), '0'), '.') . ' KB';
    }

    return $bytes . ' bytes';
}

function clamp_int(mixed $value, int $min, int $max, int $default): int
{
    if (!is_numeric($value)) {
        return $default;
    }

    return max($min, min($max, (int) $value));
}

function allowed_background(string $value): string
{
    return in_array($value, ['white', 'black', 'light'], true) ? $value : 'white';
}

function background_css(string $value): string
{
    return match (allowed_background($value)) {
        'black' => '#111111',
        'light' => '#f2f2f2',
        default => '#ffffff',
    };
}

function text_color_for_background(string $value): string
{
    return allowed_background($value) === 'black' ? '#ffffff' : '#1f1f1f';
}

function public_header(string $title): void
{
    $siteTitle = (string) app_config('site_title', 'Vertical Scroll Comic CMS');
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . e($title) . ' - ' . e($siteTitle) . '</title>';
    echo '<link rel="stylesheet" href="/assets/viewer.css">';
    echo '<script src="/assets/viewer.js" defer></script>';
    echo '</head><body><header class="site-header"><a class="brand" href="/">' . e($siteTitle) . '</a>';
    echo '<a class="admin-link" href="/admin/">Admin</a></header><main>';
}

function public_footer(): void
{
    echo '</main></body></html>';
}

function admin_header(string $title): void
{
    require_admin();
    $siteTitle = (string) app_config('site_title', 'Vertical Scroll Comic CMS');
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>' . e($title) . ' - Admin - ' . e($siteTitle) . '</title>';
    echo '<link rel="stylesheet" href="/assets/admin.css">';
    echo '<script src="/assets/admin.js" defer></script>';
    echo '</head><body><header class="admin-header">';
    echo '<a class="brand" href="/admin/">Admin</a><nav>';
    echo '<a href="/admin/series_edit.php">New series</a>';
    echo '<a href="/">Public site</a>';
    echo '<a href="/admin/logout.php">Logout</a>';
    echo '</nav></header><main class="admin-main">';
    echo '<h1>' . e($title) . '</h1>';

    $flash = flash();
    if ($flash !== null) {
        echo '<div class="alert">' . e($flash) . '</div>';
    }
}

function admin_footer(): void
{
    echo '</main></body></html>';
}

function status_badge(string $status): string
{
    $class = $status === 'published' ? 'badge badge-published' : 'badge badge-draft';
    return '<span class="' . $class . '">' . e($status) . '</span>';
}
