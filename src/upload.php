<?php

declare(strict_types=1);

function normalize_uploaded_files(array $files): array
{
    if (!isset($files['name']) || !is_array($files['name'])) {
        return [$files];
    }

    $normalized = [];
    foreach ($files['name'] as $index => $name) {
        $normalized[] = [
            'name' => $name,
            'type' => $files['type'][$index] ?? '',
            'tmp_name' => $files['tmp_name'][$index] ?? '',
            'error' => $files['error'][$index] ?? UPLOAD_ERR_NO_FILE,
            'size' => $files['size'][$index] ?? 0,
        ];
    }

    return $normalized;
}

function save_uploaded_image(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        throw new RuntimeException('No file was uploaded.');
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        $name = (string) ($file['name'] ?? 'file');
        $message = match ($file['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => $name . ' exceeds the PHP upload limit of ' . human_bytes(effective_upload_max_bytes()) . '.',
            UPLOAD_ERR_PARTIAL => $name . ' was only partially uploaded.',
            default => 'Upload failed for ' . $name . '.',
        };
        throw new RuntimeException($message);
    }

    $maxBytes = effective_upload_max_bytes();
    if ((int) $file['size'] <= 0 || (int) $file['size'] > $maxBytes) {
        throw new RuntimeException('The image is empty or exceeds the upload size limit of ' . human_bytes($maxBytes) . '.');
    }

    $originalName = basename((string) $file['name']);
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($extension, $allowedExtensions, true)) {
        throw new RuntimeException('Only JPEG, PNG, WebP, and GIF uploads are allowed.');
    }

    $tmpName = (string) $file['tmp_name'];
    $imageInfo = @getimagesize($tmpName);
    if ($imageInfo === false) {
        throw new RuntimeException('The uploaded file is not a valid image.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = $finfo ? (string) finfo_file($finfo, $tmpName) : (string) ($imageInfo['mime'] ?? '');

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($mimeType, $allowedMimes, true)) {
        throw new RuntimeException('The uploaded file type is not allowed.');
    }

    $storedExtension = $extension === 'jpeg' ? 'jpg' : $extension;
    $storedName = bin2hex(random_bytes(16)) . '.' . $storedExtension;
    $uploadDir = BASE_PATH . '/public/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $destination = $uploadDir . '/' . $storedName;
    if (!move_uploaded_file($tmpName, $destination)) {
        throw new RuntimeException('Could not save uploaded file.');
    }

    return [
        'original_name' => $originalName,
        'stored_name' => $storedName,
        'relative_path' => 'uploads/' . $storedName,
        'mime_type' => $mimeType,
        'file_size' => (int) $file['size'],
        'width' => (int) ($imageInfo[0] ?? 0),
        'height' => (int) ($imageInfo[1] ?? 0),
    ];
}
