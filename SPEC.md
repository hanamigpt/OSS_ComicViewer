# Vertical Scroll Comic CMS MVP Specification

A lightweight open-source PHP + SQLite CMS for creators who want to publish or preview vertical-scroll comics on their own site.

## Scope

The MVP includes:

- Single-user admin login with PHP sessions.
- Series create, edit, delete, draft, and publish controls.
- Episode create, edit, delete, draft, and publish controls.
- Ordered episode blocks for images, spacers, and text.
- Upload validation for JPEG, PNG, WebP, and GIF images.
- A public home page, series page, and vertical-scroll reader.
- Responsive, mobile-first reader layout.

The MVP intentionally excludes multi-user accounts, comments, rankings, payments, analytics, platform imports, AI features, and a plugin system.

## Stack

- PHP 8.2+
- SQLite 3 through PDO
- HTML, CSS, and vanilla JavaScript
- No Composer dependency required for v1

## Local Commands

```bash
cp config/config.example.php config/config.php
php scripts/create_password_hash.php "your-password"
php scripts/init_db.php
php -S localhost:8000 -t public
```

## Data Model

Core tables:

- `series`
- `episodes`
- `assets`
- `episode_blocks`

`episode_blocks` supports three block types:

- `image`
- `spacer`
- `text`

## Definition of Done

The MVP is done when a maintainer can initialize the database, start the PHP built-in server, log into admin, create a series and episode, upload multiple panel images, reorder blocks, add spacer/text blocks, publish the work, and read it in the public vertical-scroll viewer.
