<?php

class FileHandler
{
    public function getDirectoryTree(string $path, string $relativePath = '', int $depth = 0): string
    {
        $html = '<ul>';
        $items = scandir($path);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $itemPath = $path . DIRECTORY_SEPARATOR . $item;
            $itemRelativePath = ltrim($relativePath . '/' . $item, '/');

            if (is_dir($itemPath)) {
                $html .= '<li class="folder" data-depth="' . $depth . '">' . htmlspecialchars($item);
                $html .= $this->getDirectoryTree($itemPath, $itemRelativePath, $depth + 1);
                $html .= '</li>';
            } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'md') {
                $html .= '<li class="file" data-depth="' . $depth . '"><a href="?file=' . urlencode($itemRelativePath) . '">' . htmlspecialchars($item) . '</a></li>';
            }
        }

        $html .= '</ul>';
        return $html;
    }

    public function getFile(string $filePath): string
    {
        // Überprüfen, ob die Datei eine .md-Datei ist
        if (pathinfo($filePath, PATHINFO_EXTENSION) !== 'md') {
            return '<p style="color:red;">Fehler: Datei nicht gefunden oder nicht erlaubt.</p>';
        }

        // Pfad validieren und Datei lesen
        if (!file_exists($filePath)) {
            return '<p style="color:red;">Fehler: Datei nicht gefunden.</p>';
        }

        return file_get_contents($filePath);
    }
}