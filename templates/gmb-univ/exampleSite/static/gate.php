<?php
define('CDN_BASE_URL', 'https://cdn-hub.buzz/images/');
define('CACHE_DIR', __DIR__ . '/cache_images/'); // Папка для кэша

if (!is_dir(CACHE_DIR)) mkdir(CACHE_DIR, 0755, true);

$imagePath = $_GET['path'] ?? '';
if (empty($imagePath) || strpos($imagePath, '..') !== false) {
    http_response_code(400);
    exit;
}

$extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
$mimeTypeMap = [
    'webp' => 'image/webp',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'svg'  => 'image/svg+xml',
];

if (!isset($mimeTypeMap[$extension])) {
    http_response_code(404);
    exit;
}

$cacheFile = CACHE_DIR . md5($imagePath) . '.' . $extension;

if (file_exists($cacheFile)) {
    header('Content-Type: ' . $mimeTypeMap[$extension]);
    header('Cache-Control: max-age=31536000, public');
    readfile($cacheFile);
    exit;
}

// Если нет в кэше — качаем с CDN
$fullImageUrl = CDN_BASE_URL . $imagePath;
$ch = curl_init($fullImageUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 10
]);
$imageData = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200 && !empty($imageData)) {
    file_put_contents($cacheFile, $imageData); // сохраняем
    header('Content-Type: ' . $mimeTypeMap[$extension]);
    header('Cache-Control: max-age=31536000, public');
    echo $imageData;
} else {
    http_response_code(404);
}
exit;