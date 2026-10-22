<?php
require_once __DIR__ . '/config/bootstrap.php';

$fileName = basename($_GET['file'] ?? '');
$isPreview = isset($_GET['preview']) && $_GET['preview'] == '1';
$bgColor = $_GET['bg'] ?? 'transparent';

$filePath = PROCESSED_PATH . $fileName;

if (empty($fileName) || !file_exists($filePath)) {
    http_response_code(404);
    exit('File not found.');
}

// 1. 如果是透明背景预览或直接下载原图，无需 GD 转换，直接安全输出
if ($bgColor === 'transparent') {
    header('Content-Type: image/png');
    if (!$isPreview) {
        header('Content-Disposition: attachment; filename="removebg_' . time() . '.png"');
    }
    readfile($filePath);
    exit;
}

// 2. 如果用户选择了自定义背景色（白色、黑色或自定义颜色），则使用 GD 库合成背景
if (!extension_loaded('gd')) {
    http_response_code(500);
    exit('GD extension is not loaded.');
}

$image = @imagecreatefrompng($filePath);
if (!$image) {
    header('Content-Type: image/png');
    readfile($filePath);
    exit;
}

$width = imagesx($image);
$height = imagesy($image);
$outputImage = imagecreatetruecolor($width, $height);
imagesavealpha($outputImage, true);

// 解析背景颜色
if ($bgColor === 'white') {
    $bg = imagecolorallocate($outputImage, 255, 255, 255);
} elseif ($bgColor === 'black') {
    $bg = imagecolorallocate($outputImage, 0, 0, 0);
} else {
    $hex = ltrim($bgColor, '#');
    if (strlen($hex) === 6) {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $bg = imagecolorallocate($outputImage, $r, $g, $b);
    } else {
        $bg = imagecolorallocate($outputImage, 255, 255, 255);
    }
}

imagefilledrectangle($outputImage, 0, 0, $width, $height, $bg);
imagecopy($outputImage, $image, 0, 0, 0, 0, $width, $height);

header('Content-Type: image/png');
if (!$isPreview) {
    header('Content-Disposition: attachment; filename="removebg_' . time() . '.png"');
}
imagepng($outputImage);
imagedestroy($outputImage);
imagedestroy($image);
exit;