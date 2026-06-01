<?php

declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';

$seriesList = all_series_admin();

admin_header('Series');
?>
<div class="toolbar">
    <a class="button" href="/admin/series_edit.php">Create series</a>
</div>

<?php if ($seriesList === []): ?>
    <p class="empty-state">No series yet.</p>
<?php else: ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Sort</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($seriesList as $series): ?>
                    <tr>
                        <td><?= e($series['title']) ?></td>
                        <td><code><?= e($series['slug']) ?></code></td>
                        <td><?= status_badge($series['status']) ?></td>
                        <td><?= e($series['sort_order']) ?></td>
                        <td class="actions">
                            <a href="<?= e(url_path('/admin/series_edit.php', ['id' => $series['id']])) ?>">Edit</a>
                            <a href="<?= e(url_path('/admin/episodes.php', ['series_id' => $series['id']])) ?>">Episodes</a>
                            <?php if ($series['status'] === 'published'): ?>
                                <a href="<?= e(url_path('/series.php', ['slug' => $series['slug']])) ?>">Public</a>
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
