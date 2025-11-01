<?php

namespace MiniCrawler;

use PDO;

class Repository
{
    private PDO $pdo;

    public function __construct(string $sqlitePath)
    {
        $this->pdo = new PDO('sqlite:' . $sqlitePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $this->migrate();
    }

    private function migrate(): void
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                url TEXT UNIQUE NOT NULL,
                title TEXT,
                price TEXT,
                availability TEXT,
                fetched_at TEXT NOT NULL
            )'
        );
    }

    public function upsertProduct(string $url, ?string $title, ?string $price, ?string $availability): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO products (url, title, price, availability, fetched_at)
             VALUES (:url, :title, :price, :availability, :fetched_at)
             ON CONFLICT(url) DO UPDATE SET
                title=excluded.title,
                price=excluded.price,
                availability=excluded.availability,
                fetched_at=excluded.fetched_at'
        );

        $stmt->execute([
            ':url' => $url,
            ':title' => $title,
            ':price' => $price,
            ':availability' => $availability,
            ':fetched_at' => date('c'),
        ]);
    }
}


