<?php

declare(strict_types=1);

require_once __DIR__ . '/../_bootstrap.php';

$seriesId = (int) ($_GET['series_id'] ?? 0);
$series = find_series_by_id($seriesId);
if ($series === null) {
    http_response_code(404);
    admin_header('Series not found');
    echo '<p>Series not found.</p>';
    admin_footer();
    exit;
}

$episodes = episodes_for_series_admin($seriesId);

admin_header('Episodes');
?>
<p><a href="/admin/">Back to series</a></p>
<div class="toolbar">
    <a class="button" href="<?= e(url_path('/admin/episode_edit.php', ['series_id' => $seriesId])) ?>">Create episode</a>
    <a class="button secondary" href="<?= e(url_path('/admin/series_edit.php', ['id' => $seriesId])) ?>">Edit series</a>
</div>

<section class="summary-card">
    <h2><?= e($series['title']) ?> <?= status_badge($series['status']) ?></h2>
    <?php if (!empty($series['description'])): ?>
        <p><?= nl2br(e($series['description'])) ?></p>
    <?php endif; ?>
</section>

<?php if ($episodes === []): ?>
    <p class="empty-state">No episodes yet.</p>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>No.</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($episodes as $episode): ?>
                    <tr>
                        <td><?= e($episode['title']) ?></td>
                        <td><code><?= e($episode['slug']) ?></code></td>
                        <td><?= e($episode['episode_number'] ?? '') ?></td>
                        <td><?= status_badge($episode['status']) ?></td>
                        <td class="actions">
                            <a href="<?= e(url_path('/admin/episode_edit.php', ['id' => $episode['id']])) ?>">Edit</a>
                            <a href="<?= e(url_path('/admin/blocks.php', ['episode_id' => $episode['id']])) ?>">Blocks</a>
                            <?php if ($series['status'] === 'published' && $episode['status'] === 'published'): ?>
                                <a href="<?= e(url_path('/read.php', ['series' => $series['slug'], 'episode' => $episode['slug']])) ?>">Public</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php
admin_footer();
