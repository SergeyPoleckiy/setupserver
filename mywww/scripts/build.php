<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Scripts\Config;
use Scripts\Index;
use Scripts\Builder;

$start = microtime(true);

echo "=== Сборка статического сайта ===\n";
echo "Время начала: " . date('Y-m-d H:i:s') . "\n\n";

$index = new Index();
$builder = new Builder();

// 1. Получаем все серии
echo "[1/3] Получение списка серий...\n";
$allSeries = $index->getSeries();
echo "  Найдено серий: " . count($allSeries) . "\n";

// 2. Получаем все опубликованные главы
echo "[2/3] Получение списка глав...\n";
$allChapters = $index->getAllPublished();
echo "  Найдено глав: " . count($allChapters) . "\n";

// 3. Строим индексные страницы
echo "[3/3] Генерация страниц...\n";

echo "  - Главная / архив / библиотека...\n";
$builder->buildIndexPages($allSeries, $allChapters);

// 4. Строим каждую серию
foreach ($allSeries as $series) {
    echo "  - Серия: {$series['title']}...\n";
    $chapters = $index->getChapters($series['slug']);
    $builder->buildSeries($series['slug'], $series, $chapters);
    echo "    Обработано глав: " . count($chapters) . "\n";
}

$elapsed = round(microtime(true) - $start, 3);
echo "\n=== Сборка завершена за {$elapsed} с ===\n";
