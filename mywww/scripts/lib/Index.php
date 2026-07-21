<?php

namespace Scripts;

class Index
{
    private Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Получить список всех серий
     */
    public function getSeries(): array
    {
        $series = [];
        $files = glob(Config::DATA_DIR . '/series/*.md');
        if ($files === false) {
            return [];
        }

        foreach ($files as $file) {
            $slug = basename($file, '.md');
            $data = $this->parser->parseFile($file);
            
            $series[] = [
                'slug' => $slug,
                'title' => $data['frontmatter']['title'] ?? $slug,
                'prerequisites' => $data['frontmatter']['prerequisites'] ?? [],
                'description' => $data['body'],
                'raw_description' => $data['raw_body']
            ];
        }

        return $series;
    }

    /**
     * Получить серию по slug
     */
    public function getSeriesBySlug(string $slug): ?array
    {
        $file = Config::DATA_DIR . "/series/{$slug}.md";
        if (!file_exists($file)) {
            return null;
        }

        $data = $this->parser->parseFile($file);
        return [
            'slug' => $slug,
            'title' => $data['frontmatter']['title'] ?? $slug,
            'prerequisites' => $data['frontmatter']['prerequisites'] ?? [],
            'description' => $data['body'],
            'raw_description' => $data['raw_body']
        ];
    }

    /**
     * Получить список глав для серии (отсортированы по order)
     */
    public function getChapters(string $seriesSlug): array
    {
        $chapters = [];
        $files = glob(Config::DATA_DIR . "/chapters/{$seriesSlug}--*.md");
        if ($files === false) {
            return [];
        }

        foreach ($files as $file) {
            $filename = basename($file, '.md');
            // filename имеет формат {seriesSlug}--{chapterSlug}
            $parts = explode('--', $filename, 2);
            $chapterSlug = $parts[1] ?? '';

            $data = $this->parser->parseFile($file);
            $chapters[] = [
                'slug' => $chapterSlug,
                'series' => $seriesSlug,
                'title' => $data['frontmatter']['title'] ?? $chapterSlug,
                'date' => $data['frontmatter']['date'] ?? '',
                'order' => $data['frontmatter']['order'] ?? 0,
                'tags' => $data['frontmatter']['tags'] ?? [],
                'status' => $data['frontmatter']['status'] ?? 'draft',
                'body' => $data['body'],
                'raw_body' => $data['raw_body']
            ];
        }

        // Сортировка по order
        usort($chapters, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        return $chapters;
    }

    /**
     * Получить конкретную главу
     */
    public function getChapter(string $seriesSlug, string $chapterSlug): ?array
    {
        $file = Config::DATA_DIR . "/chapters/{$seriesSlug}--{$chapterSlug}.md";
        if (!file_exists($file)) {
            return null;
        }

        $data = $this->parser->parseFile($file);
        return [
            'slug' => $chapterSlug,
            'series' => $seriesSlug,
            'title' => $data['frontmatter']['title'] ?? $chapterSlug,
            'date' => $data['frontmatter']['date'] ?? '',
            'order' => $data['frontmatter']['order'] ?? 0,
            'tags' => $data['frontmatter']['tags'] ?? [],
            'status' => $data['frontmatter']['status'] ?? 'draft',
            'body' => $data['body'],
            'raw_body' => $data['raw_body']
        ];
    }

    /**
     * Получить все опубликованные главы по всем сериям (отсортированы по дате от новых к старым)
     */
    public function getAllPublished(): array
    {
        $chapters = [];
        $files = glob(Config::DATA_DIR . "/chapters/*.md");
        if ($files === false) {
            return [];
        }

        foreach ($files as $file) {
            $filename = basename($file, '.md');
            $parts = explode('--', $filename, 2);
            $seriesSlug = $parts[0] ?? '';
            $chapterSlug = $parts[1] ?? '';

            $data = $this->parser->parseFile($file);
            if (($data['frontmatter']['status'] ?? 'draft') !== 'published') {
                continue;
            }

            $chapters[] = [
                'slug' => $chapterSlug,
                'series' => $seriesSlug,
                'title' => $data['frontmatter']['title'] ?? $chapterSlug,
                'date' => $data['frontmatter']['date'] ?? '',
                'order' => $data['frontmatter']['order'] ?? 0,
                'tags' => $data['frontmatter']['tags'] ?? [],
                'status' => 'published',
                'body' => $data['body'],
                'raw_body' => $data['raw_body']
            ];
        }

        // Сортировка по дате (в обратном порядке, то есть свежие сверху)
        usort($chapters, function ($a, $b) {
            return strcmp($b['date'], $a['date']);
        });

        return $chapters;
    }

    /**
     * Получить прогресс чтения серии
     */
    public function getProgress(string $seriesSlug): array
    {
        $chapters = $this->getChapters($seriesSlug);
        $total = count($chapters);
        $published = 0;

        foreach ($chapters as $chapter) {
            if ($chapter['status'] === 'published') {
                $published++;
            }
        }

        return [
            'published' => $published,
            'total' => $total
        ];
    }
}
