<?php

require_once 'Parsedown.php';
require_once 'ParsedownExtra.php';

class MarkdownParser
{
    private $parsedown;

    public function __construct()
    {
        $this->parsedown = new ParsedownExtra();
        $this->parsedown->setSafeMode(true); // Safe Mode bleibt aktiv
    }

    public function render(string $markdown): string
    {
        // Erlaube HTML-Tags in spezifischen Blöcken
        $markdown = $this->allowHtmlBlocks($markdown);
        return $this->parsedown->text($markdown);
    }

    private function allowHtmlBlocks(string $markdown): string
    {
        // HTML-Blöcke unverändert lassen
        return preg_replace_callback('/<([a-z]+)(.*?)>(.*?)<\/\1>/is', function ($matches) {
            return $matches[0]; // Rückgabe des unveränderten HTML-Blocks
        }, $markdown);
    }
}
