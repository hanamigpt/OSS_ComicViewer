<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

$seriesList = published_series();

public_header('Home');
?>
<section class="hero">
    <h1>Vertical-scroll comics, self-hosted.</h1>
    <p>A small reader for creator-owned long-scroll comic episodes.</p>
</section>

<section class="series-grid" aria-label="Published series">
    <?php if ($seriesList === []): ?>
        <p class="empty-state">No published series yet.</p>
    <?php endif; ?>

    <?php foreach ($seriesList as $series): ?>
        <article class="series-card">
            <?php if (!empty($series['cover_path'])): ?>
                <img src="<?= e(asset_url($series['cover_path'])) ?>" alt="" loading="lazy">
            <?php endif; ?>
            <div>
                <h2><a href="<?= e(url_path('/series.php', ['slug' => $series['slug']])) ?>"><?= e($series['title']) ?></a></h2>
                <?php if (!empty($series['description'])): ?>
                    <p><?= e(excerpt($series['description'])) ?></p>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</section>
<?php
public_footer();
