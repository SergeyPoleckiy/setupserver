<?php

/**
 * Dev-сервер для разработки PHP-блога.
 * Запуск: php scripts/serve.php
 * После изменения контента или шаблонов — перезапустите скрипт.
 * Сервер доступен по адресу: http://localhost:8080
 * Админ-панель: http://localhost:8080/admin/
 */

$host = '0.0.0.0';
$port = 8080;
$docRoot = __DIR__ . '/../public';
$router = __DIR__ . '/../router.php';

echo "=== Dev-сервер ===\n";
echo "Адрес:    http://localhost:{$port}\n";
echo "Админка:  http://localhost:{$port}/admin/\n";
echo "Корень:   {$docRoot}\n";
echo "Роутер:   {$router}\n";
echo "Для остановки нажмите Ctrl+C\n\n";

passthru("php -S {$host}:{$port} -t {$docRoot} {$router}");
