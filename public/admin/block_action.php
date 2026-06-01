<?php

declare(strict_types=1);

require_once __DIR__ . '/../../src/bootstrap.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/');
}

$episodeId = (int) ($_POST['episode_id'] ?? $_GET['episode_id'] ?? 0);
$contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
$postMaxBytes = php_post_max_bytes();
if ($postMaxBytes !== null && $postMaxBytes > 0 && $contentLength > $postMaxBytes && $_POST === []) {
    flash('Upload request was larger than PHP post_max_size (' . human_bytes($postMaxBytes) . '). Reduce image size/count or increase PHP settings.');
    redirect($episodeId > 0 ? url_path('/admin/blocks.php', ['episode_id' => $episodeId]) : '/admin/');
}

csrf_verify();

$episode = find_episode_by_id($episodeId);
if ($episode === null) {
    flash('Episode not found.');
    redirect('/admin/');
}

$action = (string) ($_POST['action'] ?? '');
$redirectUrl = url_path('/admin/blocks.php', ['episode_id' => $episodeId]);

try {
    if ($action === 'upload_images') {
        $files = normalize_uploaded_files($_FILES['panel_images'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
        $count = 0;

        foreach ($files as $file) {
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $assetId = add_asset(save_uploaded_image($file));
            add_block([
                'episode_id' => $episodeId,
                'type' => 'image',
                'asset_id' => $assetId,
                'settings_json' => encode_settings([
                    'gap_before_px' => 0,
                    'gap_after_px' => 0,
                    'alt_text' => '',
                ]),
                'sort_order' => next_block_sort_order($episodeId),
            ]);
            $count++;
        }

        if ($count === 0) {
            throw new RuntimeException('Choose at least one image to upload.');
        }

        flash($count . ' image block(s) added.');
        redirect($redirectUrl);
    }

    if ($action === 'add_spacer') {
        add_block([
            'episode_id' => $episodeId,
            'type' => 'spacer',
            'settings_json' => encode_settings([
                'height_px' => clamp_int($_POST['height_px'] ?? 120, 20, 1200, 120),
                'background' => allowed_background((string) ($_POST['background'] ?? 'white')),
            ]),
            'sort_order' => next_block_sort_order($episodeId),
        ]);

        flash('Spacer block added.');
        redirect($redirectUrl);
    }

    if ($action === 'add_text') {
        $bodyText = trim((string) ($_POST['body_text'] ?? ''));
        if ($bodyText === '') {
            throw new RuntimeException('Text block body is required.');
        }

        add_block([
            'episode_id' => $episodeId,
            'type' => 'text',
            'body_text' => $bodyText,
            'settings_json' => encode_settings([
                'align' => in_array(($_POST['align'] ?? 'center'), ['left', 'center'], true) ? $_POST['align'] : 'center',
                'size' => in_array(($_POST['size'] ?? 'normal'), ['small', 'normal', 'large'], true) ? $_POST['size'] : 'normal',
                'background' => allowed_background((string) ($_POST['background'] ?? 'white')),
            ]),
            'sort_order' => next_block_sort_order($episodeId),
        ]);

        flash('Text block added.');
        redirect($redirectUrl);
    }

    $blockId = (int) ($_POST['block_id'] ?? 0);
    $block = find_block($blockId);
    if ($block === null || (int) $block['episode_id'] !== $episodeId) {
        throw new RuntimeException('Block not found.');
    }

    if ($action === 'move_up' || $action === 'move_down') {
        move_block($episodeId, $blockId, $action === 'move_up' ? 'up' : 'down');
        flash('Block moved.');
        redirect($redirectUrl);
    }

    if ($action === 'delete_block') {
        delete_block($blockId);
        flash('Block deleted.');
        redirect($redirectUrl);
    }

    if ($action === 'update_block') {
        if ($block['type'] === 'image') {
            update_block($blockId, [
                'settings_json' => encode_settings([
                    'gap_before_px' => clamp_int($_POST['gap_before_px'] ?? 0, 0, 400, 0),
                    'gap_after_px' => clamp_int($_POST['gap_after_px'] ?? 0, 0, 400, 0),
                    'alt_text' => trim((string) ($_POST['alt_text'] ?? '')),
                ]),
            ]);
        } elseif ($block['type'] === 'spacer') {
            update_block($blockId, [
                'settings_json' => encode_settings([
                    'height_px' => clamp_int($_POST['height_px'] ?? 120, 20, 1200, 120),
                    'background' => allowed_background((string) ($_POST['background'] ?? 'white')),
                ]),
            ]);
        } elseif ($block['type'] === 'text') {
            $bodyText = trim((string) ($_POST['body_text'] ?? ''));
            if ($bodyText === '') {
                throw new RuntimeException('Text block body is required.');
            }

            update_block($blockId, [
                'body_text' => $bodyText,
                'settings_json' => encode_settings([
                    'align' => in_array(($_POST['align'] ?? 'center'), ['left', 'center'], true) ? $_POST['align'] : 'center',
                    'size' => in_array(($_POST['size'] ?? 'normal'), ['small', 'normal', 'large'], true) ? $_POST['size'] : 'normal',
                    'background' => allowed_background((string) ($_POST['background'] ?? 'white')),
                ]),
            ]);
        }

        flash('Block updated.');
        redirect($redirectUrl);
    }

    throw new RuntimeException('Unknown block action.');
} catch (Throwable $exception) {
    flash($exception->getMessage());
    redirect($redirectUrl);
}
