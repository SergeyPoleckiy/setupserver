<?php

namespace Admin;

use Scripts\Index;
use Scripts\Parser;
use Scripts\Config;

class Crud
{
    private Parser $parser;
    private Index $index;

    public function __construct()
    {
        $this->parser = new Parser();
        $this->index = new Index();
    }

    public function getSeries(): array
    {
        return $this->index->getSeries();
    }

    public function getSeriesBySlug(string $slug): ?array
    {
        return $this->index->getSeriesBySlug($slug);
    }

    public function saveSeries(string $slug, string $title, string $description, array $prerequisites = []): void
    {
        $prereqsStr = '[' . implode(', ', array_map(function ($p) {
            return '"' . str_replace('"', '\\"', $p) . '"';
        }, $prerequisites)) . ']';

        $content = "---\n"
            . "title: \"{$title}\"\n"
            . "prerequisites: {$prereqsStr}\n"
            . "---\n\n"
            . $description;

        $filePath = Config::DATA_DIR . "/series/{$slug}.md";
        file_put_contents($filePath, $content);
    }

    public function deleteSeries(string $slug): void
    {
        $filePath = Config::DATA_DIR . "/series/{$slug}.md";
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function getChapters(string $seriesSlug): array
    {
        return $this->index->getChapters($seriesSlug);
    }

    public function getChapter(string $seriesSlug, string $chapterSlug): ?array
    {
        return $this->index->getChapter($seriesSlug, $chapterSlug);
    }

    public function saveChapter(string $seriesSlug, string $chapterSlug, array $data): void
    {
        $tagsStr = '[' . implode(', ', array_map(function ($t) {
            return '"' . str_replace('"', '\\"', $t) . '"';
        }, $data['tags'] ?? [])) . ']';

        $content = "---\n"
            . "title: \"{$data['title']}\"\n"
            . "date: \"{$data['date']}\"\n"
            . "order: {$data['order']}\n"
            . "tags: {$tagsStr}\n"
            . "status: {$data['status']}\n"
            . "---\n\n"
            . $data['body'];

        $filePath = Config::DATA_DIR . "/chapters/{$seriesSlug}--{$chapterSlug}.md";
        file_put_contents($filePath, $content);
    }

    public function deleteChapter(string $seriesSlug, string $chapterSlug): void
    {
        $filePath = Config::DATA_DIR . "/chapters/{$seriesSlug}--{$chapterSlug}.md";
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function rebuild(): array
    {
        $output = [];
        $returnVar = 0;
        exec('php ' . Config::SCRIPTS_DIR . '/build.php 2>&1', $output, $returnVar);
        return [
            'success' => $returnVar === 0,
            'output' => implode("\n", $output),
        ];
    }
}
