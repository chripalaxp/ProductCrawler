<?php

// Load HTML parser library
$parserPath = __DIR__ . '/../library/simple_html_dom/simple_html_dom.php';
if (!file_exists($parserPath)) {
    fwrite(STDERR, "Missing HTML parser library at: $parserPath\n");
    exit(1);
}
require_once $parserPath;

// Load application classes
require_once __DIR__ . '/../src/Logger.php';
require_once __DIR__ . '/../src/Fetcher.php';
require_once __DIR__ . '/../src/Parser.php';
require_once __DIR__ . '/../src/Repository.php';

use MiniCrawler\Logger;
use MiniCrawler\Fetcher;
use MiniCrawler\Parser;
use MiniCrawler\Repository;

$baseDir = dirname(__DIR__);
$storageDir = $baseDir . DIRECTORY_SEPARATOR . 'logs';
if (!is_dir($storageDir)) {
    @mkdir($storageDir, 0777, true);
}
$dbPath = $storageDir . DIRECTORY_SEPARATOR . 'products.sqlite';
$logPath = $storageDir . DIRECTORY_SEPARATOR . 'log.txt';

$logger = new Logger($logPath);
$fetcher = new Fetcher();
$parser = new Parser();
$repo = new Repository($dbPath);

// Load URLs from urls.txt
$urlsFile = $baseDir . DIRECTORY_SEPARATOR . 'urls.txt';
$urls = [];
if (file_exists($urlsFile)) {
    $lines = file($urlsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line !== '' && str_starts_with($line, '#') === false) {
            $urls[] = $line;
        }
    }
}
if (empty($urls)) {
    fwrite(STDERR, "Missing Urls.\n");
    exit(1);
}

foreach ($urls as $url) {
    $html = null;
    try {
        $html = $fetcher->fetch($url);
    } catch (\Throwable $e) {
        $logger->error('Fetch failed after retry for ' . $url . ': ' . $e->getMessage());
        continue;
    }

    $data = $parser->parseProduct($html);

    foreach (['title', 'price', 'availability'] as $field) {
        $val = $data[$field] ?? null;
        if ($val === null || $val === '') {
            $logger->warning('Missing field "' . $field . '" for ' . $url);
        }
    }

    try {
        $repo->upsertProduct($url, $data['title'] ?? null, $data['price'] ?? null, $data['availability'] ?? null);
    } catch (\Throwable $e) {
        $logger->error('DB write error for ' . $url . ': ' . $e->getMessage());
        continue;
    }
}

echo "Done. Check products.sqlite and log.txt\n";


