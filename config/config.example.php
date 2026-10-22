<?php
return [
    'app_env' => 'production',
    'provider' => 'Pixelcut',
    'api_key' => 'your_pixelcut_api_key_here',
    'api_url' => 'https://api.developer.pixelcut.ai/v1/remove-background',
    'max_upload_size' => 10 * 1024 * 1024,
    'max_image_width' => 4096,
    'max_image_height' => 4096,
    'max_image_pixels' => 25 * 1024 * 1024,
    'temp_file_retention_minutes' => 60,
    'api_timeout_seconds' => 30,
];