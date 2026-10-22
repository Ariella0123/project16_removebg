<?php
require_once __DIR__ . '/config/bootstrap.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    verify_csrf_token($input['csrf_token'] ?? '');
    check_rate_limit(10, 60);

    $fileId = basename($input['file_id'] ?? '');
    $originalPath = ORIGINALS_PATH . $fileId;

    if (empty($fileId) || !file_exists($originalPath)) {
        throw new Exception('Original image not found or expired.');
    }

    $service = new BackgroundRemovalService($config);
    $processedBinary = $service->process($originalPath);

    $processedFileName = 'processed_' . bin2hex(random_bytes(16)) . '.png';
    $processedPath = PROCESSED_PATH . $processedFileName;

    if (file_put_contents($processedPath, $processedBinary) === false) {
        throw new Exception('Failed to save processed image.');
    }

    clean_temp_files();

    json_response([
        'success' => true,
        'processed_file_id' => $processedFileName,
        'preview_url' => 'download.php?file=' . urlencode($processedFileName) . '&preview=1'
    ]);

} catch (Exception $e) {
    write_log('Processing Error: ' . $e->getMessage(), 'ERROR');
    json_response([
        'success' => false,
        'error' => $e->getMessage()
    ], 400);
}