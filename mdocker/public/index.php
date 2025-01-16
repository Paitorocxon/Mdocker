<?php
require_once '../src/MarkdownParser.php';
require_once '../src/FileHandler.php';

$docsPath = realpath('../docs'); // Dokumentationsverzeichnis
$file = $_GET['file'] ?? 'index.md'; // Datei, die geladen werden soll
$filePath = $docsPath . DIRECTORY_SEPARATOR . $file;

// Sicherstellen, dass nur .md-Dateien geladen werden
if (pathinfo($file, PATHINFO_EXTENSION) !== 'md') {
    http_response_code(403);
    echo "Invalid file type! Only .md files are allowed.";
    exit;
}

// XHR-API-Anfrage
if (isset($_GET['xhr']) && $_GET['xhr'] === 'true') {
    if (!is_file($filePath)) {
        http_response_code(404);
        echo "File not found!";
        exit;
    }

    $markdownParser = new MarkdownParser();
    echo $markdownParser->render(file_get_contents($filePath));
    exit;
}

// Initiales Laden der Seite
$markdownParser = new MarkdownParser();
$content = is_file($filePath) ? $markdownParser->render(file_get_contents($filePath)) : "<p style='color:red;'>File not found!</p>";
$fileHandler = new FileHandler();
$navigationTree = $fileHandler->getDirectoryTree($docsPath);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <!-- <link rel="stylesheet" href="https://unpkg.com/highlightjs@9.16.2/styles/night-owl.css"> -->
    <link rel="stylesheet" href="https://unpkg.com/highlightjs@9.16.2/styles/a11y-dark.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>

    <title>Documentation Viewer</title>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <h1>Files</h1>
            <nav>
                <ul id="navTree">
                    <?= $navigationTree ?>
                </ul>
            </nav>
        </aside>
        <main>
            <article id="content">
                <?= $content ?>
            </article>
        </main>
    </div>
    <script src="script.js"></script>
    <script>hljs.highlightAll();</script>
</body>
</html>
