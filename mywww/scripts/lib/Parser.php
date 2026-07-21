<?php

namespace Scripts;

use League\CommonMark\GithubFlavoredMarkdownConverter;

class Parser
{
    private ?GithubFlavoredMarkdownConverter $converter = null;

    private function getConverter(): GithubFlavoredMarkdownConverter
    {
        if ($this->converter === null) {
            $this->converter = new GithubFlavoredMarkdownConverter([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);
        }
        return $this->converter;
    }

    /**
     * Парсинг YAML-шапки
     */
    public function parseFrontMatter(string $content): array
    {
        $result = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            $parts = explode(':', $line, 2);
            if (count($parts) < 2) {
                continue;
            }

            $key = trim($parts[0]);
            $val = trim($parts[1]);

            // Обработка кавычек
            if (str_starts_with($val, '"') && str_ends_with($val, '"')) {
                $val = substr($val, 1, -1);
            } elseif (str_starts_with($val, "'") && str_ends_with($val, "'")) {
                $val = substr($val, 1, -1);
            }

            // Обработка массивов [val1, val2]
            if (str_starts_with($val, '[') && str_ends_with($val, ']')) {
                $arrayContent = trim(substr($val, 1, -1));
                if ($arrayContent === '') {
                    $result[$key] = [];
                } else {
                    $items = explode(',', $arrayContent);
                    $result[$key] = array_map(function ($item) {
                        $item = trim($item);
                        if (str_starts_with($item, '"') && str_ends_with($item, '"')) {
                            $item = substr($item, 1, -1);
                        } elseif (str_starts_with($item, "'") && str_ends_with($item, "'")) {
                            $item = substr($item, 1, -1);
                        }
                        return $item;
                    }, $items);
                }
            } else {
                // Приведение к числам
                if (is_numeric($val)) {
                    if (str_contains($val, '.')) {
                        $result[$key] = (float)$val;
                    } else {
                        $result[$key] = (int)$val;
                    }
                } elseif (strtolower($val) === 'true') {
                    $result[$key] = true;
                } elseif (strtolower($val) === 'false') {
                    $result[$key] = false;
                } elseif (strtolower($val) === 'null') {
                    $result[$key] = null;
                } else {
                    $result[$key] = $val;
                }
            }
        }

        return $result;
    }

    /**
     * Рендеринг Markdown в HTML через CommonMark v2 API
     */
    public function renderMarkdown(string $text): string
    {
        return $this->getConverter()->convert($text)->getContent();
    }

    /**
     * Открыть файл, распарсить frontmatter + тело
     */
    public function parseFile(string $path): array
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException("Файл не найден: " . $path);
        }

        $content = file_get_contents($path);
        
        // Разделяем frontmatter и body
        // Шапка находится между первыми двумя вхождениями ---
        $pattern = "/^---\s*\n(.*?)\n---\s*\n(.*)/s";
        if (preg_match($pattern, $content, $matches)) {
            $frontmatterRaw = $matches[1];
            $bodyRaw = $matches[2];
        } else {
            $frontmatterRaw = '';
            $bodyRaw = $content;
        }

        $frontmatter = $this->parseFrontMatter($frontmatterRaw);
        $bodyHtml = $this->renderMarkdown($bodyRaw);

        return [
            'frontmatter' => $frontmatter,
            'body' => $bodyHtml,
            'raw_body' => $bodyRaw
        ];
    }
}
