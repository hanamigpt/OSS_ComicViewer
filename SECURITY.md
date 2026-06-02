# Security Policy

OSS_ComicViewer is currently an MVP intended for local development and small self-hosted experiments.

Do not expose the admin area to the public internet without reviewing deployment security.

For Xserver deployment notes, see [DEPLOY_XSERVER.md](DEPLOY_XSERVER.md).

## Before Public Deployment

Please review at least:

- HTTPS configuration.
- Secure session cookies.
- A strong admin password.
- Upload directory execution restrictions.
- Backup strategy for the SQLite database and uploaded files.
- File upload limits.
- File permissions for `data/` and `public/uploads/`.
- Keeping `config/config.php`, SQLite database files, and production uploads out of git.

## Reporting a Vulnerability

Please do not disclose sensitive security issues in a public issue. Contact the maintainer privately if a private contact route is available.
