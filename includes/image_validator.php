<?php
function validate_uploaded_image($file, $config) {
    if (!isset($file['error']) || is_array($file['error'])) {
        throw new Exception('Invalid upload parameters.');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload failed with error code: ' . $file['error']);
    }

    if ($file['size'] > $config['max_upload_size']) {
        throw new Exception('File size exceeds the maximum allowed limit.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mimeType, $allowedMimes)) {
        throw new Exception('Invalid file format. Only JPEG, PNG, and WebP are supported.');
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        throw new Exception('The uploaded file is not a valid image.');
    }

    list($width, $height) = $imageInfo;
    if ($width > $config['max_image_width'] || $height > $config['max_image_height']) {
        throw new Exception('Image dimensions exceed maximum limits.');
    }

    if (($width * $height) > $config['max_image_pixels']) {
        throw new Exception('Image total pixel count exceeds allowed limits.');
    }

    return true;
}