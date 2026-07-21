<?php

/**
 * Dev-роутер: перенаправляет /admin/ в admin/index.php,
 * остальное — обслуживает из public/
 */

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Маршрутизация /admin/
if (str_starts_with($requestUri, '/admin/') || $requestUri === '/admin') {
    $_SERVER['SCRIPT_NAME'] = '/admin/index.php';
    $_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/admin/index.php';
    require __DIR__ . '/admin/index.php';
    return true;
}

// Статика из public/
$docRoot = __DIR__ . '/public';
$filePath = $docRoot . $requestUri;

if (is_file($filePath)) {
    return false; // отдать файл
}

// Если файла нет — отдать index.html (для SPA-like навигации)
$indexFile = $docRoot . $requestUri . '/index.html';
if (is_file($indexFile)) {
    require $indexFile;
    return true;
}

// fallback на index.html
require $docRoot . '/index.html';
return true;
