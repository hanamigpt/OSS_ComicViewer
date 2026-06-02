# Contributing

Thank you for your interest in OSS_ComicViewer.

This project aims to stay small: a lightweight PHP + SQLite CMS and reader for creator-owned vertical-scroll comics.

## Current Scope

In scope:

- PHP + SQLite CMS basics.
- Series and episode management.
- Image, spacer, and text blocks.
- Public vertical-scroll reader.
- Small self-hosted or local preview workflows.

Out of scope for now:

- AI features.
- Multi-user accounts.
- Comments, rankings, or social features.
- Payments, ads, or subscriptions.
- Analytics.
- Plugin systems.
- Complex animation engines.
- Platform imports.

## Before Opening a PR

Please keep changes small and readable. Prefer one focused change per PR.

For larger proposals, open an issue first and describe:

- what problem it solves,
- why it belongs in the small MVP scope,
- how it affects setup, security, or maintenance.

## Local Checks

Before submitting a change, run:

```bash
find . -name '*.php' -not -path './doc/*' -not -path './.git/*' -exec php -l {} \;
php scripts/init_db.php
php -S localhost:8000 -t public
```

Then confirm the admin and public reader still load.
