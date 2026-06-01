<?php

declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';

$episodeId = (int) ($_GET['episode_id'] ?? 0);
$episode = find_episode_by_id($episodeId);
if ($episode === null) {
    http_response_code(404);
    admin_header('Episode not found');
    echo '<p>Episode not found.</p>';
    admin_footer();
    exit;
}

$series = find_series_by_id((int) $episode['series_id']);
$blocks = blocks_for_episode($episodeId);

admin_header('Episode blocks');
?>
<p>
    <a href="<?= e(url_path('/admin/episodes.php', ['series_id' => $episode['series_id']])) ?>">Back to episodes</a>
    <?php if ($series !== null && $series['status'] === 'published' && $episode['status'] === 'published'): ?>
        · <a href="<?= e(url_path('/read.php', ['series' => $series['slug'], 'episode' => $episode['slug']])) ?>">Public reader</a>
    <?php endif; ?>
</p>

<section class="summary-card">
    <h2><?= e($episode['title']) ?> <?= status_badge($episode['status']) ?></h2>
    <?php if ($series !== null): ?>
        <p><?= e($series['title']) ?></p>
    <?php endif; ?>
</section>

<div class="editor-grid">
    <section class="form-card">
        <h2>Upload image blocks</h2>
        <p class="muted">
            Max per image: <?= e(human_bytes(effective_upload_max_bytes())) ?>.
            PHP request limit: <?= e(php_post_max_bytes() === null ? 'unlimited' : human_bytes(php_post_max_bytes())) ?>.
        </p>
        <form method="post" action="<?= e(url_path('/admin/block_action.php', ['episode_id' => $episodeId])) ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="episode_id" value="<?= e($episodeId) ?>">
            <input type="hidden" name="action" value="upload_images">
            <label>
                Panel images
                <input type="file" name="panel_images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple required>
            </label>
            <button type="submit">Upload panels</button>
        </form>
    </section>

    <section class="form-card">
        <h2>Add spacer</h2>
        <form method="post" action="/admin/block_action.php">
            <?= csrf_field() ?>
            <input type="hidden" name="episode_id" value="<?= e($episodeId) ?>">
            <input type="hidden" name="action" value="add_spacer">
            <div class="field-row">
                <label>
                    Height
                    <input type="number" name="height_px" value="120" min="20" max="1200">
                </label>
                <label>
                    Background
                    <select name="background">
                        <option value="white">White</option>
                        <option value="black">Black</option>
                        <option value="light">Light gray</option>
                    </select>
                </label>
            </div>
            <button type="submit">Add spacer</button>
        </form>
    </section>

    <section class="form-card">
        <h2>Add text</h2>
        <form method="post" action="/admin/block_action.php">
            <?= csrf_field() ?>
            <input type="hidden" name="episode_id" value="<?= e($episodeId) ?>">
            <input type="hidden" name="action" value="add_text">
            <label>
                Text
                <textarea name="body_text" rows="4" required></textarea>
            </label>
            <div class="field-row">
                <label>
                    Align
                    <select name="align">
                        <option value="center">Center</option>
                        <option value="left">Left</option>
                    </select>
                </label>
                <label>
                    Size
                    <select name="size">
                        <option value="normal">Normal</option>
                        <option value="small">Small</option>
                        <option value="large">Large</option>
                    </select>
                </label>
                <label>
                    Background
                    <select name="background">
                        <option value="white">White</option>
                        <option value="black">Black</option>
                        <option value="light">Light gray</option>
                    </select>
                </label>
            </div>
            <button type="submit">Add text</button>
        </form>
    </section>
</div>

<section class="block-list">
    <h2>Ordered blocks</h2>
    <?php if ($blocks === []): ?>
        <p class="empty-state">No blocks yet. Upload panel images or add a spacer/text block.</p>
    <?php endif; ?>

    <?php foreach ($blocks as $index => $block): ?>
        <?php $settings = decode_settings($block['settings_json']); ?>
        <article class="block-item">
            <div class="block-head">
                <h3>#<?= e($index + 1) ?> <?= e(ucfirst($block['type'])) ?></h3>
                <div class="block-actions">
                    <form method="post" action="/admin/block_action.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="episode_id" value="<?= e($episodeId) ?>">
                        <input type="hidden" name="block_id" value="<?= e($block['id']) ?>">
                        <button type="submit" name="action" value="move_up">Up</button>
                    </form>
                    <form method="post" action="/admin/block_action.php">
                        <?= csrf_field() ?>
                        <input type="hidden" name="episode_id" value="<?= e($episodeId) ?>">
                        <input type="hidden" name="block_id" value="<?= e($block['id']) ?>">
                        <button type="submit" name="action" value="move_down">Down</button>
                    </form>
                    <form method="post" action="/admin/block_action.php" data-confirm="Delete this block?">
                        <?= csrf_field() ?>
                        <input type="hidden" name="episode_id" value="<?= e($episodeId) ?>">
                        <input type="hidden" name="block_id" value="<?= e($block['id']) ?>">
                        <button type="submit" name="action" value="delete_block" class="danger">Delete</button>
                    </form>
                </div>
            </div>

            <?php if ($block['type'] === 'image'): ?>
                <?php if (!empty($block['relative_path'])): ?>
                    <img class="panel-thumb" src="<?= e(asset_url($block['relative_path'])) ?>" alt="">
                    <p class="muted"><?= e($block['original_name']) ?> · <?= e($block['width']) ?>x<?= e($block['height']) ?></p>
                <?php else: ?>
                    <p class="muted">Image asset missing.</p>
                <?php endif; ?>
                <form method="post" action="/admin/block_action.php" class="inline-editor">
                    <?= csrf_field() ?>
                    <input type="hidden" name="episode_id" value="<?= e($episodeId) ?>">
                    <input type="hidden" name="block_id" value="<?= e($block['id']) ?>">
                    <input type="hidden" name="action" value="update_block">
                    <div class="field-row">
                        <label>
                            Gap before
                            <input type="number" name="gap_before_px" value="<?= e($settings['gap_before_px'] ?? 0) ?>" min="0" max="400">
                        </label>
                        <label>
                            Gap after
                            <input type="number" name="gap_after_px" value="<?= e($settings['gap_after_px'] ?? 0) ?>" min="0" max="400">
                        </label>
                    </div>
                    <label>
                        Alt text
                        <input type="text" name="alt_text" value="<?= e($settings['alt_text'] ?? '') ?>">
                    </label>
                    <button type="submit">Update image settings</button>
                </form>
            <?php elseif ($block['type'] === 'spacer'): ?>
                <div class="spacer-preview" style="height: <?= e(clamp_int($settings['height_px'] ?? 120, 20, 1200, 120)) ?>px; background: <?= e(background_css((string) ($settings['background'] ?? 'white'))) ?>;"></div>
                <form method="post" action="/admin/block_action.php" class="inline-editor">
                    <?= csrf_field() ?>
                    <input type="hidden" name="episode_id" value="<?= e($episodeId) ?>">
                    <input type="hidden" name="block_id" value="<?= e($block['id']) ?>">
                    <input type="hidden" name="action" value="update_block">
                    <div class="field-row">
                        <label>
                            Height
                            <input type="number" name="height_px" value="<?= e($settings['height_px'] ?? 120) ?>" min="20" max="1200">
                        </label>
                        <label>
                            Background
                            <select name="background">
                                <option value="white"<?= selected($settings['background'] ?? 'white', 'white') ?>>White</option>
                                <option value="black"<?= selected($settings['background'] ?? 'white', 'black') ?>>Black</option>
                                <option value="light"<?= selected($settings['background'] ?? 'white', 'light') ?>>Light gray</option>
                            </select>
                        </label>
                    </div>
                    <button type="submit">Update spacer</button>
                </form>
            <?php elseif ($block['type'] === 'text'): ?>
                <form method="post" action="/admin/block_action.php" class="inline-editor">
                    <?= csrf_field() ?>
                    <input type="hidden" name="episode_id" value="<?= e($episodeId) ?>">
                    <input type="hidden" name="block_id" value="<?= e($block['id']) ?>">
                    <input type="hidden" name="action" value="update_block">
                    <label>
                        Text
                        <textarea name="body_text" rows="4" required><?= e($block['body_text']) ?></textarea>
                    </label>
                    <div class="field-row">
                        <label>
                            Align
                            <select name="align">
                                <option value="center"<?= selected($settings['align'] ?? 'center', 'center') ?>>Center</option>
                                <option value="left"<?= selected($settings['align'] ?? 'center', 'left') ?>>Left</option>
                            </select>
                        </label>
                        <label>
                            Size
                            <select name="size">
                                <option value="normal"<?= selected($settings['size'] ?? 'normal', 'normal') ?>>Normal</option>
                                <option value="small"<?= selected($settings['size'] ?? 'normal', 'small') ?>>Small</option>
                                <option value="large"<?= selected($settings['size'] ?? 'normal', 'large') ?>>Large</option>
                            </select>
                        </label>
                        <label>
                            Background
                            <select name="background">
                                <option value="white"<?= selected($settings['background'] ?? 'white', 'white') ?>>White</option>
                                <option value="black"<?= selected($settings['background'] ?? 'white', 'black') ?>>Black</option>
                                <option value="light"<?= selected($settings['background'] ?? 'white', 'light') ?>>Light gray</option>
                            </select>
                        </label>
                    </div>
                    <button type="submit">Update text</button>
                </form>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</section>
<?php
admin_footer();
