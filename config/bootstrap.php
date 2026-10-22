<?php
session_start();

function load_env($filePath) {
    if (!file_exists($filePath)) {
        return;
    }
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, '"\'');
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv("{$name}={$value}");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

load_env(dirname(__DIR__) . '/.env');

$config = [
    'app_env' => getenv('APP_ENV') ?: 'production',
    'provider' => getenv('BACKGROUND_REMOVAL_PROVIDER') ?: 'Pixelcut',
    'api_key' => getenv('BACKGROUND_REMOVAL_API_KEY') ?: '',
    'api_url' => getenv('BACKGROUND_REMOVAL_API_URL') ?: 'https://api.developer.pixelcut.ai/v1/remove-background',
    'max_upload_size' => (int)(getenv('MAX_UPLOAD_SIZE') ?: (10 * 1024 * 1024)),
    'max_image_width' => (int)(getenv('MAX_IMAGE_WIDTH') ?: 4096),
    'max_image_height' => (int)(getenv('MAX_IMAGE_HEIGHT') ?: 4096),
    'max_image_pixels' => (int)(getenv('MAX_IMAGE_PIXELS') ?: (25 * 1024 * 1024)),
    'temp_file_retention_minutes' => (int)(getenv('TEMP_FILE_RETENTION_MINUTES') ?: 60),
    'api_timeout_seconds' => (int)(getenv('API_TIMEOUT_SECONDS') ?: 30),
];

if ($config['app_env'] === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

define('STORAGE_PATH', dirname(__DIR__) . '/storage/');
define('ORIGINALS_PATH', STORAGE_PATH . 'originals/');
define('PROCESSED_PATH', STORAGE_PATH . 'processed/');
define('LOGS_PATH', STORAGE_PATH . 'logs/');

foreach ([ORIGINALS_PATH, PROCESSED_PATH, LOGS_PATH, STORAGE_PATH . 'rate_limits/'] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/image_validator.php';
require_once dirname(__DIR__) . '/includes/csrf.php';
require_once dirname(__DIR__) . '/includes/rate_limiter.php';
require_once dirname(__DIR__) . '/includes/cleanup.php';
require_once dirname(__DIR__) . '/services/BackgroundRemovalService.php';
require_once dirname(__DIR__) . '/services/providers/PixelcutProvider.php';