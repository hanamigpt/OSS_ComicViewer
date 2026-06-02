# Deploying OSS_ComicViewer on Xserver

## Status

OSS_ComicViewer is an MVP for local development and small self-hosted experiments. Review security before exposing admin routes to the public internet.

The safest first deployment mode is:

- Use a test subdomain.
- Enable HTTPS and always-on SSL.
- Enable BASIC authentication for the whole subdomain at first.
- Keep the app's `/admin/` login enabled.
- Keep Xserver WAF enabled unless a specific false positive is confirmed.
- Back up the SQLite database and uploaded files together.

## Recommended Directory Layout

Do not copy the whole repository into `public_html`.

Only the contents of `public/` should be web-accessible.

When copying `public/` into `public_html`, include hidden files such as `.htaccess`.

Recommended layout:

```text
example.com/
  app/
    config/
      config.php
      config.example.php
    data/
      app.sqlite
    database/
    scripts/
    src/
    README.md
    SECURITY.md
    DEPLOY_XSERVER.md

  public_html/
    _bootstrap.php
    index.php
    series.php
    read.php
    .htaccess
    admin/
    assets/
    uploads/
```

With this layout, `public_html/_bootstrap.php` automatically looks for:

```text
../app/src/bootstrap.php
```

If your app directory uses another name or path, set this in `public_html/.htaccess`:

```apache
SetEnv OSS_COMICVIEWER_BOOTSTRAP /home/YOUR_ACCOUNT/YOUR_DOMAIN/app/src/bootstrap.php
```

## Files That Must Not Be Web-Accessible

Keep these outside `public_html`:

- `config/config.php`
- `data/app.sqlite`
- `src/`
- `scripts/`
- `database/`
- `.git/`

The repository includes `.htaccess` files for basic Apache protection, but directory layout is the main protection. Do not rely on `.htaccess` alone to hide secrets.

## Production Config

Create `app/config/config.php` from `config/config.example.php`.

Example for the recommended layout:

```php
<?php

return [
    'database_path' => __DIR__ . '/../data/app.sqlite',
    'upload_dir' => __DIR__ . '/../../public_html/uploads',
    'upload_url_path' => 'uploads',
    'admin_username' => 'admin',
    'admin_password_hash' => 'replace-with-password_hash-output',
    'upload_max_bytes' => 10 * 1024 * 1024,
    'site_title' => 'Vertical Scroll Comic CMS',
    'timezone' => 'Asia/Tokyo',
    'secure_cookies' => true,
];
```

Use:

```bash
php scripts/create_password_hash.php "your-strong-password"
```

Then paste the generated hash into `admin_password_hash`.

## Xserver Settings Checklist

- [ ] Use PHP 8.2 or newer.
- [ ] Confirm PDO SQLite / SQLite3 is enabled.
- [ ] Confirm file uploads are enabled.
- [ ] Confirm `upload_max_filesize`.
- [ ] Confirm `post_max_size`.
- [ ] Enable HTTPS.
- [ ] Enable always-on SSL.
- [ ] Enable BASIC authentication for the whole test subdomain at first, or at least `/admin/`.
- [ ] Keep WAF enabled unless a specific false positive is confirmed.
- [ ] Use a strong, unique admin password.
- [ ] Enable two-factor authentication for Xserver and GitHub accounts.

## `.htaccess` Notes

The repository includes:

- `public/.htaccess`
- `public/admin/.htaccess`
- `public/uploads/.htaccess`

These are copied into `public_html` with the rest of `public/`.

They are intended to:

- disable directory listing,
- deny access to hidden files,
- deny common database / backup file extensions,
- prevent PHP-family files from running inside `uploads/`.

If Xserver returns a 500 error after upload, temporarily remove the newest `.htaccess` directive and re-test. Some Apache directives can vary by hosting environment.

## Initialize the Database

Run initialization from the non-public app directory:

```bash
cd /home/YOUR_ACCOUNT/YOUR_DOMAIN/app
php scripts/init_db.php
```

If shell access is unavailable, initialize locally and upload `data/app.sqlite` to the non-public `app/data/` directory.

## Backups

Back up these together:

- `app/config/config.php`
- `app/data/app.sqlite`
- `public_html/uploads/`

The SQLite database stores series, episode, asset, and block records. Uploaded image files live separately. Restoring only one side can break reader images.

## Manual Deployment Test

- [ ] Public home loads over HTTPS.
- [ ] BASIC authentication appears where expected.
- [ ] `/admin/` loads only after BASIC authentication if enabled.
- [ ] Invalid admin login fails.
- [ ] Valid admin login succeeds.
- [ ] A series can be created.
- [ ] An episode can be created.
- [ ] Image upload works.
- [ ] Spacer and text blocks work.
- [ ] Blocks can be reordered.
- [ ] Published reader page shows uploaded images.
- [ ] Draft series and episodes are hidden publicly.
- [ ] `config/config.php` is not reachable by URL.
- [ ] `data/app.sqlite` is not reachable by URL.
- [ ] `src/` is not reachable by URL.
- [ ] PHP files placed in `uploads/` do not execute.

## Troubleshooting

### 500 error after upload

Check `.htaccess` first. If the error started after adding or editing `.htaccess`, remove the newest directive and re-test.

### Images do not upload

Check:

- `file_uploads`
- `upload_max_filesize`
- `post_max_size`
- write permission for `public_html/uploads/`
- WAF false positives

### SQLite errors

Check:

- PDO SQLite / SQLite3 extension availability
- write permission for `app/data/`
- `database_path` in `config/config.php`

### Login works over HTTP but not HTTPS

For production, keep HTTPS enabled and set:

```php
'secure_cookies' => true,
```

Then clear old cookies and log in again.
