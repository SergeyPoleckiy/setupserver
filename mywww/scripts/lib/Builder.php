<?php

namespace Scripts;

class Builder
{
    /**
     * Экранирование HTML
     */
    public function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Рендеринг шаблона с данными
     */
    public function renderTemplate(string $templateName, array $data): string
    {
        // Делаем глобальные переменные доступными во всех шаблонах
        $globalData = [
            'siteTitle' => Config::SITE_TITLE,
            'siteUrl' => Config::SITE_URL,
        ];
        
        $mergedData = array_merge($globalData, $data);
        extract($mergedData);

        ob_start();
        $templatePath = Config::TEMPLATES_DIR . '/' . $templateName;
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("Шаблон не найден: " . $templatePath);
        }
        require $templatePath;
        return ob_get_clean();
    }

    /**
     * Рендеринг полноценной страницы (шаблон контента + layout)
     */
    public function buildPage(string $contentTemplate, array $data): string
    {
        // Сначала рендерим контент страницы
        $contentHtml = $this->renderTemplate($contentTemplate, $data);
        
        // Затем оборачиваем его в layout
        $layoutData = array_merge($data, [
            'content' => $contentHtml,
        ]);
        
        return $this->renderTemplate('layout.php', $layoutData);
    }

    /**
     * Атомарная запись файла через временный файл
     */
    public function writeFile(string $path, string $content): void
    {
        $this->ensureDirectory(dirname($path));

        $tempFile = tempnam(dirname($path), 'tmp_render_');
        if ($tempFile === false) {
            throw new \RuntimeException("Не удалось создать временный файл в " . dirname($path));
        }

        if (file_put_contents($tempFile, $content) === false) {
            unlink($tempFile);
            throw new \RuntimeException("Не удалось записать данные во временный файл: " . $tempFile);
        }

        // Выставляем права доступа, чтобы Nginx мог прочитать сгенерированную статику
        chmod($tempFile, 0644);

        if (!rename($tempFile, $path)) {
            unlink($tempFile);
            throw new \RuntimeException("Не удалось атомарно заменить файл " . $path);
        }
    }

    /**
     * Создать папку, если она не существует
     */
    public function ensureDirectory(string $path): void
    {
        if (!is_dir($path)) {
            if (!mkdir($path, 0755, true) && !is_dir($path)) {
                throw new \RuntimeException("Не удалось создать директорию: " . $path);
            }
        }
    }

    /**
     * Рекурсивное удаление директории
     */
    public function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            $filePath = $path . '/' . $file;
            if (is_dir($filePath)) {
                $this->removeDirectory($filePath);
            } else {
                unlink($filePath);
            }
        }
        rmdir($path);
    }

    /**
     * Построить страницу серии и все страницы глав этой серии
     */
    public function buildSeries(string $seriesSlug, array $series, array $chapters): void
    {
        $indexObj = new Index();
        $progress = $indexObj->getProgress($seriesSlug);

        // 1. Строим страницу серии
        $seriesData = [
            'activeNav' => 'series',
            'series' => $series,
            'chapters' => $chapters,
            'progress' => $progress,
        ];
        
        $seriesHtml = $this->buildPage('series-single.php', $seriesData);
        $this->writeFile(Config::PUBLIC_DIR . "/{$seriesSlug}/index.html", $seriesHtml);

        // 2. Строим страницы глав этой серии
        $publishedChapters = array_filter($chapters, function ($ch) {
            return $ch['status'] === 'published';
        });
        $publishedChapters = array_values($publishedChapters); // сброс ключей для навигации по индексам

        foreach ($publishedChapters as $index => $chapter) {
            $prevChapter = $publishedChapters[$index - 1] ?? null;
            $nextChapter = $publishedChapters[$index + 1] ?? null;

            $chapterData = [
                'activeNav' => 'series',
                'series' => $series,
                'chapter' => $chapter,
                'prevChapter' => $prevChapter,
                'nextChapter' => $nextChapter,
            ];

            $chapterHtml = $this->buildPage('chapter.php', $chapterData);
            $this->writeFile(Config::PUBLIC_DIR . "/{$seriesSlug}/{$chapter['slug']}/index.html", $chapterHtml);
        }
    }

    /**
     * Построить индексные страницы (home, library, archive)
     */
    public function buildIndexPages(array $allSeries, array $allChapters): void
    {
        $indexObj = new Index();

        // 1. Главная страница (home.php)
        // Блок "Читаю сейчас" — выведем серии с прогрессом
        $seriesWithProgress = [];
        foreach ($allSeries as $series) {
            $progress = $indexObj->getProgress($series['slug']);
            $seriesWithProgress[] = array_merge($series, [
                'progress' => $progress,
            ]);
        }

        // Блок "Новые главы" — 5 последних опубликованных глав
        $newChapters = array_slice($allChapters, 0, 5);

        $homeHtml = $this->buildPage('home.php', [
            'activeNav' => 'home',
            'series' => $seriesWithProgress,
            'newChapters' => $newChapters,
        ]);
        $this->writeFile(Config::PUBLIC_DIR . '/index.html', $homeHtml);

        // 2. Библиотека серий (series.php)
        $seriesHtml = $this->buildPage('series.php', [
            'activeNav' => 'series',
            'series' => $seriesWithProgress,
        ]);
        $this->writeFile(Config::PUBLIC_DIR . '/series/index.html', $seriesHtml);

        // 3. Архив (archive.php)
        $archiveHtml = $this->buildPage('archive.php', [
            'activeNav' => 'archive',
            'chapters' => $allChapters,
        ]);
        $this->writeFile(Config::PUBLIC_DIR . '/archive/index.html', $archiveHtml);
    }
}
