<?php

declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';

$seriesSlug = (string) ($_GET['series'] ?? '');
$episodeSlug = (string) ($_GET['episode'] ?? '');
$episode = find_published_episode($seriesSlug, $episodeSlug);

if ($episode === null) {
    http_response_code(404);
    public_header('Episode not found');
    echo '<section class="reader-shell"><h1>Episode not found</h1><p>This episode is not published or does not exist.</p></section>';
    public_footer();
    exit;
}

$blocks = blocks_for_episode((int) $episode['id']);
$previous = adjacent_published_episode((int) $episode['series_id'], (int) $episode['id'], 'previous');
$next = adjacent_published_episode((int) $episode['series_id'], (int) $episode['id'], 'next');

public_header($episode['title']);
?>
<section class="reader-shell">
    <nav class="reader-nav">
        <a href="<?= e(url_path('/series.php', ['slug' => $episode['series_slug']])) ?>">Back to episodes</a>
    </nav>

    <header class="reader-title">
        <p><?= e($episode['series_title']) ?></p>
        <h1><?= e($episode['title']) ?></h1>
        <?php if (!empty($episode['description'])): ?>
            <div><?= nl2br(e($episode['description'])) ?></div>
        <?php endif; ?>
    </header>

    <article class="comic-column" aria-label="Comic episode">
        <?php foreach ($blocks as $block): ?>
            <?php
            $settings = decode_settings($block['settings_json']);
            $background = background_css((string) ($settings['background'] ?? 'white'));
            $color = text_color_for_background((string) ($settings['background'] ?? 'white'));
            ?>
            <?php if ($block['type'] === 'image' && !empty($block['relative_path'])): ?>
                <?php
                $gapBefore = clamp_int($settings['gap_before_px'] ?? 0, 0, 400, 0);
                $gapAfter = clamp_int($settings['gap_after_px'] ?? 0, 0, 400, 0);
                ?>
                <figure class="comic-block comic-image" style="margin-top: <?= $gapBefore ?>px; margin-bottom: <?= $gapAfter ?>px;">
                    <img
                        src="<?= e(asset_url($block['relative_path'])) ?>"
                        alt="<?= e($settings['alt_text'] ?? '') ?>"
                        width="<?= e($block['width'] ?? '') ?>"
                        height="<?= e($block['height'] ?? '') ?>"
                        loading="lazy"
                    >
                </figure>
            <?php elseif ($block['type'] === 'spacer'): ?>
                <?php $height = clamp_int($settings['height_px'] ?? 120, 20, 1200, 120); ?>
                <div class="comic-block comic-spacer" style="height: <?= $height ?>px; background: <?= e($background) ?>;"></div>
            <?php elseif ($block['type'] === 'text'): ?>
                <?php
                $align = in_array(($settings['align'] ?? 'center'), ['left', 'center'], true) ? $settings['align'] : 'center';
                $size = in_array(($settings['size'] ?? 'normal'), ['small', 'normal', 'large'], true) ? $settings['size'] : 'normal';
                ?>
                <div class="comic-block comic-text text-<?= e($size) ?>" style="text-align: <?= e($align) ?>; background: <?= e($background) ?>; color: <?= e($color) ?>;">
                    <?= nl2br(e($block['body_text'])) ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </article>

    <nav class="episode-pager" aria-label="Episode navigation">
        <?php if ($previous !== null): ?>
            <a href="<?= e(url_path('/read.php', ['series' => $episode['series_slug'], 'episode' => $previous['slug']])) ?>">Previous</a>
        <?php else: ?>
            <span></span>
        <?php endif; ?>

        <?php if ($next !== null): ?>
            <a href="<?= e(url_path('/read.php', ['series' => $episode['series_slug'], 'episode' => $next['slug']])) ?>">Next</a>
        <?php endif; ?>
    </nav>
</section>
<?php
public_footer();
