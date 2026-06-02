# Progress

## 2026-06-02

- Added git ignore rules for private `doc/`, local config, SQLite database, and uploads.
- Added public README, SPEC, MIT LICENSE, config example, database schema, and scaffold directories.
- Implemented SQLite bootstrap, auth, CSRF, repository helpers, and upload validation.
- Implemented admin series, episode, and block editing screens.
- Implemented public home, series, and vertical-scroll reader pages.
- Verified `.gitignore` behavior and SQLite schema loading with `sqlite3`.
- PHP lint and local server validation are pending because `php` is not installed in the current shell environment.
- Installed PHP 8.5.6 with Homebrew.
- Verified required PHP extensions, PHP lint, and database initialization.
- Created ignored local `config/config.php` for development login with username `admin`.
- Started the PHP built-in server at `http://127.0.0.1:8000`.
- Verified public home, admin login page, successful admin login, and authenticated admin home over HTTP.
- Found Homebrew PHP defaults `upload_max_filesize=2M` and `post_max_size=8M`.
- Added visible upload limit messaging and clearer handling for PHP upload size failures.
- Added OSS-facing documentation polish:
  - `CHANGELOG.md`
  - `CONTRIBUTING.md`
  - `SECURITY.md`
  - `docs/manual-test-ja.md`
- Added README screenshot section with `docs/screenshots/reader.png`.
- Kept runtime behavior unchanged.
- Added Xserver deployment preparation:
  - `DEPLOY_XSERVER.md`
  - public bootstrap resolver for `public_html` deployments
  - configurable upload filesystem path and URL path
  - production-aware secure session cookie option
  - `.htaccess` guards for public, admin, and uploads directories
- Manual confirmation on Xserver is still required for Apache directive compatibility, WAF behavior, SQLite extensions, upload limits, and file permissions.
