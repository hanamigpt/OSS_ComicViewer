<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$series = $id ? find_series_by_id($id) : null;
if ($id && $series === null) {
    http_response_code(404);
    admin_header('Series not found');
    echo '<p>Series not found.</p>';
    admin_footer();
    exit;
}

$values = $series ?? [
    'title' => '',
    'slug' => '',
    'description' => '',
    'status' => 'draft',
    'sort_order' => 0,
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = (string) ($_POST['action'] ?? 'save');

    if ($action === 'delete' && $id !== null) {
        delete_series($id);
        flash('Series deleted.');
        redirect('/admin/');
    }

    $values = [
        'title' => trim((string) ($_POST['title'] ?? '')),
        'slug' => trim((string) ($_POST['slug'] ?? '')),
        'description' => trim((string) ($_POST['description'] ?? '')),
        'status' => (string) ($_POST['status'] ?? 'draft'),
        'sort_order' => (int) ($_POST['sort_order'] ?? 0),
    ];

    if ($values['slug'] === '') {
        $values['slug'] = slugify($values['title']);
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
            $savedId = save_series($values, $id);
            flash('Series saved.');
            redirect(url_path('/admin/episodes.php', ['series_id' => $savedId]));
        } catch (PDOException $exception) {
            $errors[] = str_contains($exception->getMessage(), 'UNIQUE') ? 'Slug is already used.' : 'Could not save series.';
        }
    }
}

admin_header($id ? 'Edit series' : 'Create series');
render_errors($errors);
?>
<form method="post" class="form-card">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <label>
        Title
        <input type="text" name="title" value="<?= e($values['title']) ?>" required>
    </label>
    <label>
        Slug
        <input type="text" name="slug" value="<?= e($values['slug']) ?>" pattern="[a-z0-9]+(-[a-z0-9]+)*">
    </label>
    <label>
        Description
        <textarea name="description" rows="6"><?= e($values['description']) ?></textarea>
    </label>
    <div class="field-row">
        <label>
            Status
            <select name="status">
                <option value="draft"<?= selected($values['status'], 'draft') ?>>Draft</option>
                <option value="published"<?= selected($values['status'], 'published') ?>>Published</option>
            </select>
        </label>
        <label>
            Sort order
            <input type="number" name="sort_order" value="<?= e($values['sort_order']) ?>">
        </label>
    </div>
    <div class="button-row">
        <button type="submit">Save series</button>
        <a class="button secondary" href="/admin/">Cancel</a>
    </div>
</form>

<?php if ($id !== null): ?>
    <form method="post" class="danger-zone" data-confirm="Delete this series and its episodes?">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="delete">
        <button type="submit" class="danger">Delete series</button>
    </form>
<?php endif; ?>
<?php
admin_footer();
