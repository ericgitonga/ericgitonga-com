<?php
declare(strict_types=1);

namespace EricGitonga\Services;

final class MarkdownParser
{
    /**
     * Parse frontmatter and markdown body.
     */
    public static function parse(string $source): array
    {
        $meta = [];
        $body = $source;

        if (preg_match('/\A---\r?\n(.*?)\r?\n---\r?\n(.*)\Z/s', $source, $m)) {
            $front = $m[1];
            $body = trim($m[2]);
            foreach (explode("\n", $front) as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, ':')) {
                    continue;
                }
                [$k, $v] = explode(':', $line, 2);
                $k = trim($k);
                $v = trim($v, " \t\n\r\0\x0B\"'");
                $meta[$k] = $v;
            }
        }

        $html = self::renderHtml($body);
        return [
            'meta' => $meta,
            'html' => $html,
            'raw_body' => $body,
        ];
    }

    public static function renderHtml(string $md): string
    {
        // Code blocks ```
        $md = preg_replace_callback('/```([a-z0-9_-]*)\r?\n(.*?)\r?\n```/s', function ($m) {
            $lang = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
            $code = htmlspecialchars($m[2], ENT_QUOTES, 'UTF-8');
            return "<pre><code class=\"language-{$lang}\">{$code}</code></pre>";
        }, $md);

        // Inline code `code`
        $md = preg_replace_callback('/`([^`]+)`/', function ($m) {
            return '<code>' . htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8') . '</code>';
        }, $md);

        // Headers
        $md = preg_replace('/^#### (.*?)$/m', '<h4>$1</h4>', $md);
        $md = preg_replace('/^### (.*?)$/m', '<h3>$1</h3>', $md);
        $md = preg_replace('/^## (.*?)$/m', '<h2>$1</h2>', $md);
        $md = preg_replace('/^# (.*?)$/m', '<h1>$1</h1>', $md);

        // Blockquotes
        $md = preg_replace('/^> (.*?)$/m', '<blockquote><p>$1</p></blockquote>', $md);

        // Unordered lists
        $md = preg_replace('/^\* (.*?)$/m', '<li>$1</li>', $md);
        $md = preg_replace('/^- (.*?)$/m', '<li>$1</li>', $md);
        $md = preg_replace('/(<li>.*?<\/li>(\r?\n)?)+/s', '<ul>$0</ul>', $md);

        // Bold & Italic
        $md = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $md);
        $md = preg_replace('/\*([^\*]+)\*/', '<em>$1</em>', $md);

        // Links [text](url)
        $md = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', function ($m) {
            $text = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
            $url = htmlspecialchars($m[2], ENT_QUOTES, 'UTF-8');
            return "<a href=\"{$url}\">{$text}</a>";
        }, $md);

        // Paragraphs
        $paragraphs = preg_split('/\r?\n\r?\n/', $md);
        $out = [];
        foreach ($paragraphs as $p) {
            $p = trim($p);
            if ($p === '') {
                continue;
            }
            if (str_starts_with($p, '<h') || str_starts_with($p, '<pre') ||
                str_starts_with($p, '<ul') || str_starts_with($p, '<block')) {
                $out[] = $p;
            } else {
                $out[] = '<p>' . nl2br($p) . '</p>';
            }
        }

        return implode("\n\n", $out);
    }
}
