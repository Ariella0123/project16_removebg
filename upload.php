<?php
require_once __DIR__ . '/config/bootstrap.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // 暂时注释掉 CSRF 校验以排查 400 错误
    // verify_csrf_token($_POST['csrf_token'] ?? '');
    
    check_rate_limit(15, 60);

    if (!isset($_FILES['image'])) {
        throw new Exception('No image file uploaded.');
    }

    validate_uploaded_image($_FILES['image'], $config);

    $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $randomName = bin2hex(random_bytes(16)) . '.' . $fileExtension;
    $destination = ORIGINALS_PATH . $randomName;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
        throw new Exception('Failed to save uploaded file on server.');
    }

    json_response([
        'success' => true,
        'file_id' => $randomName,
        'message' => 'Upload successful.'
    ]);

} catch (Exception $e) {
    json_response([
        'success' => false,
        'error' => $e->getMessage()
    ], 400);
}