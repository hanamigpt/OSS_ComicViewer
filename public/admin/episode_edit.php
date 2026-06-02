<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$episode = $id ? find_episode_by_id($id) : null;
if ($id && $episode === null) {
    http_response_code(404);
    admin_header('Episode not found');
    echo '<p>Episode not found.</p>';
    admin_footer();
    exit;
}

$seriesList = all_series_admin();
$initialSeriesId = (int) ($_GET['series_id'] ?? ($episode['series_id'] ?? 0));
$values = $episode ?? [
    'series_id' => $initialSeriesId,
    'title' => '',
    'slug' => '',
    'episode_number' => '',
    'description' => '',
    'status' => 'draft',
    'published_at' => '',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = (string) ($_POST['action'] ?? 'save');

    if ($action === 'delete' && $id !== null) {
        $redirectSeriesId = (int) $episode['series_id'];
        delete_episode($id);
        flash('Episode deleted.');
        redirect(url_path('/admin/episodes.php', ['series_id' => $redirectSeriesId]));
    }

    $episodeNumberRaw = trim((string) ($_POST['episode_number'] ?? ''));
    $values = [
        'series_id' => (int) ($_POST['series_id'] ?? 0),
        'title' => trim((string) ($_POST['title'] ?? '')),
        'slug' => trim((string) ($_POST['slug'] ?? '')),
        'episode_number' => $episodeNumberRaw === '' ? null : (int) $episodeNumberRaw,
        'description' => trim((string) ($_POST['description'] ?? '')),
        'status' => (string) ($_POST['status'] ?? 'draft'),
        'published_at' => trim((string) ($_POST['published_at'] ?? '')),
    ];

    if ($values['slug'] === '') {
        $values['slug'] = slugify($values['title']);
    }
    if ($values['published_at'] === '' && $values['status'] === 'published') {
        $values['published_at'] = now();
    }
    if ($values['published_at'] === '') {
        $values['published_at'] = null;
    }

    if (find_series_by_id((int) $values['series_id']) === null) {
        $errors[] = 'Series is required.';
    }
    if ($values['title'] === '') {
        $errors[] = 'Title is required.';
    }
    if (!is_valid_slug($values['slug'])) {
        $errors[] = 'Slug must contain lowercase letters, numbers, and hyphens.';
    }
    if (!in_array($values['status'], ['draft', 'published'], true)) {
        $errors[] = 'Status is invalid.';
    }

    if ($errors === []) {
        try {
            $savedId = save_episode($values, $id);
            flash('Episode saved.');
            redirect(url_path('/admin/blocks.php', ['episode_id' => $savedId]));
        } catch (PDOException $exception) {
            $errors[] = str_contains($exception->getMessage(), 'UNIQUE') ? 'Episode slug is already used in this series.' : 'Could not save episode.';
        }
    }
}

admin_header($id ? 'Edit episode' : 'Create episode');
render_errors($errors);
?>
<form method="post" class="form-card">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <label>
        Series
        <select name="series_id" required>
            <option value="">Select series</option>
            <?php foreach ($seriesList as $series): ?>
                <option value="<?= e($series['id']) ?>"<?= selected($values['series_id'], $series['id']) ?>><?= e($series['title']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        Title
        <input type="text" name="title" value="<?= e($values['title']) ?>" required>
    </label>
    <label>
        Slug
        <input type="text" name="slug" value="<?= e($values['slug']) ?>" pattern="[a-z0-9]+(-[a-z0-9]+)*">
    </label>
    <div class="field-row">
        <label>
            Episode number
            <input type="number" name="episode_number" value="<?= e($values['episode_number'] ?? '') ?>">
        </label>
        <label>
            Published at
            <input type="text" name="published_at" value="<?= e($values['published_at'] ?? '') ?>" placeholder="<?= e(now()) ?>">
        </label>
    </div>
    <label>
        Description
        <textarea name="description" rows="5"><?= e($values['description']) ?></textarea>
    </label>
    <label>
        Status
        <select name="status">
            <option value="draft"<?= selected($values['status'], 'draft') ?>>Draft</option>
            <option value="published"<?= selected($values['status'], 'published') ?>>Published</option>
        </select>
    </label>
    <div class="button-row">
        <button type="submit">Save episode</button>
        <?php if (!empty($values['series_id'])): ?>
            <a class="button secondary" href="<?= e(url_path('/admin/episodes.php', ['series_id' => $values['series_id']])) ?>">Cancel</a>
        <?php endif; ?>
    </div>
</form>

<?php if ($id !== null): ?>
    <form method="post" class="danger-zone" data-confirm="Delete this episode and its blocks?">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="delete">
        <button type="submit" class="danger">Delete episode</button>
    </form>
<?php endif; ?>
<?php
admin_footer();
