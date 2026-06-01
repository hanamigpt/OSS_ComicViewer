<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/bootstrap.php';

$slug = (string) ($_GET['slug'] ?? '');
$series = find_published_series_by_slug($slug);
if ($series === null) {
    http_response_code(404);
    public_header('Series not found');
    echo '<section class="reader-shell"><h1>Series not found</h1><p>This series is not published or does not exist.</p></section>';
    public_footer();
    exit;
}

$episodes = published_episodes_for_series((int) $series['id']);

public_header($series['title']);
?>
<section class="series-detail">
    <p><a href="/">Back to all series</a></p>
    <h1><?= e($series['title']) ?></h1>
    <?php if (!empty($series['description'])): ?>
        <p><?= nl2br(e($series['description'])) ?></p>
    <?php endif; ?>
</section>

<section class="episode-list" aria-label="Episodes">
    <h2>Episodes</h2>
    <?php if ($episodes === []): ?>
        <p class="empty-state">No published episodes yet.</p>
    <?php endif; ?>

    <?php foreach ($episodes as $episode): ?>
        <article class="episode-row">
            <a href="<?= e(url_path('/read.php', ['series' => $series['slug'], 'episode' => $episode['slug']])) ?>">
                <?= e($episode['title']) ?>
            </a>
            <?php if ($episode['episode_number'] !== null): ?>
                <span>Episode <?= e($episode['episode_number']) ?></span>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>
<?php
public_footer();
