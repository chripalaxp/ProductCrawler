<?php
declare(strict_types=1);

$baseDir = dirname(__DIR__);
$dbPath = $baseDir . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'products.sqlite';

if (!file_exists($dbPath)) {
    fwrite(STDERR, "products.sqlite not found. Run php bin/crawl.php first.\n");
    exit(1);
}

try {
    $pdo = new PDO('sqlite:' . $dbPath, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Throwable $e) {
    fwrite(STDERR, "Failed to open DB: " . $e->getMessage() . "\n");
    exit(1);
}

$stmt = $pdo->query('SELECT url, title, price, availability, fetched_at FROM products ORDER BY datetime(fetched_at) DESC');
$rows = $stmt ? $stmt->fetchAll() : [];

if (!$rows) {
    echo "No rows found.\n";
    exit(0);
}

// Simple table-like output
echo "URL | TITLE | PRICE | AVAILABILITY | FETCHED_AT\n";
echo str_repeat('-', 120) . "\n";
foreach ($rows as $r) {
    $url = $r['url'] ?? '';
    $title = $r['title'] ?? '';
    $price = $r['price'] ?? '';
    $avail = $r['availability'] ?? '';
    $ts = $r['fetched_at'] ?? '';
    echo $url . " | " . $title . " | " . $price . " | " . $avail . " | " . $ts . "\n";
}


