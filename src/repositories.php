<?php

declare(strict_types=1);

function all_series_admin(): array
{
    return db()->query(
        'SELECT s.*, a.relative_path AS cover_path
         FROM series s
         LEFT JOIN assets a ON a.id = s.cover_asset_id
         ORDER BY s.sort_order ASC, s.created_at DESC'
    )->fetchAll();
}

function published_series(): array
{
    $stmt = db()->prepare(
        'SELECT s.*, a.relative_path AS cover_path
         FROM series s
         LEFT JOIN assets a ON a.id = s.cover_asset_id
         WHERE s.status = :status
         ORDER BY s.sort_order ASC, s.created_at DESC'
    );
    $stmt->execute(['status' => 'published']);
    return $stmt->fetchAll();
}

function find_series_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM series WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function find_published_series_by_slug(string $slug): ?array
{
    $stmt = db()->prepare('SELECT * FROM series WHERE slug = :slug AND status = :status');
    $stmt->execute(['slug' => $slug, 'status' => 'published']);
    $row = $stmt->fetch();
    return $row ?: null;
}

function save_series(array $data, ?int $id = null): int
{
    $params = [
        'slug' => $data['slug'],
        'title' => $data['title'],
        'description' => $data['description'],
        'status' => $data['status'],
        'sort_order' => $data['sort_order'],
        'updated_at' => now(),
    ];

    if ($id !== null) {
        $params['id'] = $id;
        db()->prepare(
            'UPDATE series
             SET slug = :slug, title = :title, description = :description,
                 status = :status, sort_order = :sort_order, updated_at = :updated_at
             WHERE id = :id'
        )->execute($params);
        return $id;
    }

    $params['created_at'] = now();
    db()->prepare(
        'INSERT INTO series (slug, title, description, status, sort_order, created_at, updated_at)
         VALUES (:slug, :title, :description, :status, :sort_order, :created_at, :updated_at)'
    )->execute($params);

    return (int) db()->lastInsertId();
}

function delete_series(int $id): void
{
    db()->prepare('DELETE FROM series WHERE id = :id')->execute(['id' => $id]);
}

function episodes_for_series_admin(int $seriesId): array
{
    $stmt = db()->prepare(
        'SELECT *
         FROM episodes
         WHERE series_id = :series_id
         ORDER BY episode_number IS NULL, episode_number ASC, created_at DESC'
    );
    $stmt->execute(['series_id' => $seriesId]);
    return $stmt->fetchAll();
}

function published_episodes_for_series(int $seriesId): array
{
    $stmt = db()->prepare(
        'SELECT *
         FROM episodes
         WHERE series_id = :series_id AND status = :status
         ORDER BY episode_number IS NULL, episode_number ASC, published_at ASC, created_at ASC'
    );
    $stmt->execute(['series_id' => $seriesId, 'status' => 'published']);
    return $stmt->fetchAll();
}

function find_episode_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM episodes WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function find_published_episode(string $seriesSlug, string $episodeSlug): ?array
{
    $stmt = db()->prepare(
        'SELECT e.*, s.slug AS series_slug, s.title AS series_title, s.description AS series_description
         FROM episodes e
         INNER JOIN series s ON s.id = e.series_id
         WHERE s.slug = :series_slug
           AND e.slug = :episode_slug
           AND s.status = :series_status
           AND e.status = :episode_status'
    );
    $stmt->execute([
        'series_slug' => $seriesSlug,
        'episode_slug' => $episodeSlug,
        'series_status' => 'published',
        'episode_status' => 'published',
    ]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function save_episode(array $data, ?int $id = null): int
{
    $params = [
        'series_id' => $data['series_id'],
        'slug' => $data['slug'],
        'title' => $data['title'],
        'episode_number' => $data['episode_number'],
        'description' => $data['description'],
        'status' => $data['status'],
        'published_at' => $data['published_at'],
        'updated_at' => now(),
    ];

    if ($id !== null) {
        $params['id'] = $id;
        db()->prepare(
            'UPDATE episodes
             SET series_id = :series_id, slug = :slug, title = :title,
                 episode_number = :episode_number, description = :description,
                 status = :status, published_at = :published_at, updated_at = :updated_at
             WHERE id = :id'
        )->execute($params);
        return $id;
    }

    $params['created_at'] = now();
    db()->prepare(
        'INSERT INTO episodes
             (series_id, slug, title, episode_number, description, status, published_at, created_at, updated_at)
         VALUES
             (:series_id, :slug, :title, :episode_number, :description, :status, :published_at, :created_at, :updated_at)'
    )->execute($params);

    return (int) db()->lastInsertId();
}

function delete_episode(int $id): void
{
    db()->prepare('DELETE FROM episodes WHERE id = :id')->execute(['id' => $id]);
}

function blocks_for_episode(int $episodeId): array
{
    $stmt = db()->prepare(
        'SELECT b.*, a.relative_path, a.original_name, a.width, a.height
         FROM episode_blocks b
         LEFT JOIN assets a ON a.id = b.asset_id
         WHERE b.episode_id = :episode_id
         ORDER BY b.sort_order ASC, b.id ASC'
    );
    $stmt->execute(['episode_id' => $episodeId]);
    return $stmt->fetchAll();
}

function next_block_sort_order(int $episodeId): int
{
    $stmt = db()->prepare('SELECT COALESCE(MAX(sort_order), 0) + 10 FROM episode_blocks WHERE episode_id = :episode_id');
    $stmt->execute(['episode_id' => $episodeId]);
    return (int) $stmt->fetchColumn();
}

function add_asset(array $data): int
{
    db()->prepare(
        'INSERT INTO assets (original_name, stored_name, relative_path, mime_type, file_size, width, height, created_at)
         VALUES (:original_name, :stored_name, :relative_path, :mime_type, :file_size, :width, :height, :created_at)'
    )->execute([
        'original_name' => $data['original_name'],
        'stored_name' => $data['stored_name'],
        'relative_path' => $data['relative_path'],
        'mime_type' => $data['mime_type'],
        'file_size' => $data['file_size'],
        'width' => $data['width'],
        'height' => $data['height'],
        'created_at' => now(),
    ]);

    return (int) db()->lastInsertId();
}

function add_block(array $data): int
{
    db()->prepare(
        'INSERT INTO episode_blocks
             (episode_id, type, asset_id, body_text, settings_json, sort_order, created_at, updated_at)
         VALUES
             (:episode_id, :type, :asset_id, :body_text, :settings_json, :sort_order, :created_at, :updated_at)'
    )->execute([
        'episode_id' => $data['episode_id'],
        'type' => $data['type'],
        'asset_id' => $data['asset_id'] ?? null,
        'body_text' => $data['body_text'] ?? null,
        'settings_json' => $data['settings_json'] ?? '{}',
        'sort_order' => $data['sort_order'],
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return (int) db()->lastInsertId();
}

function update_block(int $id, array $data): void
{
    db()->prepare(
        'UPDATE episode_blocks
         SET body_text = :body_text, settings_json = :settings_json, updated_at = :updated_at
         WHERE id = :id'
    )->execute([
        'id' => $id,
        'body_text' => $data['body_text'] ?? null,
        'settings_json' => $data['settings_json'] ?? '{}',
        'updated_at' => now(),
    ]);
}

function delete_block(int $id): void
{
    db()->prepare('DELETE FROM episode_blocks WHERE id = :id')->execute(['id' => $id]);
}

function find_block(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM episode_blocks WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function move_block(int $episodeId, int $blockId, string $direction): void
{
    $blocks = blocks_for_episode($episodeId);
    $index = null;
    foreach ($blocks as $i => $block) {
        if ((int) $block['id'] === $blockId) {
            $index = $i;
            break;
        }
    }

    if ($index === null) {
        return;
    }

    $neighborIndex = $direction === 'up' ? $index - 1 : $index + 1;
    if (!isset($blocks[$neighborIndex])) {
        return;
    }

    $current = $blocks[$index];
    $neighbor = $blocks[$neighborIndex];
    $stmt = db()->prepare('UPDATE episode_blocks SET sort_order = :sort_order, updated_at = :updated_at WHERE id = :id');
    $stmt->execute([
        'sort_order' => $neighbor['sort_order'],
        'updated_at' => now(),
        'id' => $current['id'],
    ]);
    $stmt->execute([
        'sort_order' => $current['sort_order'],
        'updated_at' => now(),
        'id' => $neighbor['id'],
    ]);
}

function adjacent_published_episode(int $seriesId, int $episodeId, string $direction): ?array
{
    $episodes = published_episodes_for_series($seriesId);
    $currentIndex = null;

    foreach ($episodes as $index => $episode) {
        if ((int) $episode['id'] === $episodeId) {
            $currentIndex = $index;
            break;
        }
    }

    if ($currentIndex === null) {
        return null;
    }

    $targetIndex = $direction === 'previous' ? $currentIndex - 1 : $currentIndex + 1;
    return $episodes[$targetIndex] ?? null;
}
