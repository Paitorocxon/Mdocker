
class MarkdownParser
{
    public function render(string $markdown): string
    {
        // Schritt 1: Block-Level Parsing
        $html = $this->parseBlocks($markdown);

        // Schritt 2: Inline-Level Parsing
        $html = $this->parseInline($html);

        return $html;
    }

    private function parseBlocks(string $markdown): string
    {
        // Überschriften
        $markdown = preg_replace('/^###### (.+)/m', '<h6>$1</h6>', $markdown);
        $markdown = preg_replace('/^##### (.+)/m', '<h5>$1</h5>', $markdown);
        $markdown = preg_replace('/^#### (.+)/m', '<h4>$1</h4>', $markdown);
        $markdown = preg_replace('/^### (.+)/m', '<h3>$1</h3>', $markdown);
        $markdown = preg_replace('/^## (.+)/m', '<h2>$1</h2>', $markdown);
        $markdown = preg_replace('/^# (.+)/m', '<h1>$1</h1>', $markdown);

        // Blockzitate
        $markdown = preg_replace('/^> (.+)/m', '<blockquote>$1</blockquote>', $markdown);

        // Codeblöcke (Fenced Code Blocks)
        $markdown = preg_replace_callback('/```(\w+)?\n(.*?)```/s', function ($matches) {
            $language = $matches[1] ?? 'plaintext';
            $code = htmlspecialchars($matches[2], ENT_QUOTES, 'UTF-8');
            return '<pre><code class="language-' . $language . '">' . $code . '</code></pre>';
        }, $markdown);

        // Tabellen
        $markdown = preg_replace_callback('/^\|(.+?)\|\n\|([ :-|]+)\|\n((?:\|.+?\|\n?)+)/m', function ($matches) {
            $header = explode('|', trim($matches[1]));
            $rows = explode("\n", trim($matches[3]));
            $table = '<table><thead><tr>';
            foreach ($header as $cell) {
                $table .= '<th>' . trim($cell) . '</th>';
            }
            $table .= '</tr></thead><tbody>';
            foreach ($rows as $row) {
                if (trim($row) === '') continue;
                $table .= '<tr>';
                foreach (explode('|', trim($row, '|')) as $cell) {
                    $table .= '<td>' . trim($cell) . '</td>';
                }
                $table .= '</tr>';
            }
            $table .= '</tbody></table>';
            return $table;
        }, $markdown);

        // Listen
        $markdown = preg_replace_callback('/(^|\n)- (.+)/', function ($matches) {
            return $matches[1] . '<ul><li>' . $matches[2] . '</li></ul>';
        }, $markdown);
        $markdown = preg_replace('/<\/ul>\n<ul>/', '', $markdown);

        $markdown = preg_replace_callback('/(^|\n)\d+\. (.+)/', function ($matches) {
            return $matches[1] . '<ol><li>' . $matches[2] . '</li></ol>';
        }, $markdown);
        $markdown = preg_replace('/<\/ol>\n<ol>/', '', $markdown);

        return $markdown;
    }

    private function parseInline(string $markdown): string
    {
        // Fett und Kursiv
        $markdown = preg_replace('/\*\*\*(.+?)\*\*\*/', '<strong><em>$1</em></strong>', $markdown);
        $markdown = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $markdown);
        $markdown = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $markdown);

        // Inline-Code
        $markdown = preg_replace('/`(.+?)`/', '<code>$1</code>', $markdown);

        // Links
        $markdown = preg_replace_callback('/\[(.+?)\]\((.+?)\)/', function ($matches) {
            $url = $matches[2];
            if (pathinfo($url, PATHINFO_EXTENSION) === 'md') {
                $url = '?file=' . urlencode($url);
            }
            return '<a href="' . htmlspecialchars($url) . '">' . htmlspecialchars($matches[1]) . '</a>';
        }, $markdown);

        // Bilder
        $markdown = preg_replace('/!\[(.*?)\]\((.+?)\)/', '<img src="$2" alt="$1" loading="lazy">', $markdown);

        return nl2br($markdown);
    }
} 