<?php
// --- НАСТРОЙКА ---
// Базовый URL, откуда будем тащить картинки. Слеш в конце обязателен.
define('CDN_BASE_URL', 'https://cdn-hub.buzz/images/');

// --- БОЕВАЯ ЛОГИКА ---

// 1. Получаем путь к картинке из URL (?path=...)
$imagePath = $_GET['path'] ?? '';

// 2. Минимальная безопасность: проверяем, что путь не пустой и не содержит '..',
// чтобы через скрипт нельзя было залезть в другие папки.
if (empty($imagePath) || strpos($imagePath, '..') !== false) {
    // Если запрос кривой, отдаем ошибку.
    http_response_code(400); // Bad Request
    exit;
}

// 3. Собираем полный, финальный URL картинки на CDN.
$fullImageUrl = CDN_BASE_URL . $imagePath;

// 4. Определяем тип контента по расширению файла.
// Это нужно, чтобы браузер понял, что ему прислали именно картинку.
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
    // Если расширение файла нам неизвестно, отдаем ошибку.
    http_response_code(404); // Not Found
    exit;
}

// 5. С помощью cURL запрашиваем картинку с CDN.
$ch = curl_init($fullImageUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Получить результат, а не вывести его
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Следовать за редиректами
$imageData = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем код ответа (200 - OK, 404 - не найдено)
curl_close($ch);

// 6. Если картинка успешно получена (код 200)...
if ($httpCode === 200 && !empty($imageData)) {
    // ...отправляем браузеру правильный заголовок...
    header('Content-Type: ' . $mimeTypeMap[$extension]);
    // ...и саму картинку.
    echo $imageData;
} else {
    // Если CDN вернул ошибку, мы тоже отдаем ошибку.
    http_response_code(404); // Not Found
}

// Завершаем работу скрипта.
exit;