<?php

namespace MiniCrawler;

class Logger
{
    private string $logFile;

    public function __construct(string $logFile)
    {
        $this->logFile = $logFile;
    }

    public function info(string $message): void
    {
        $this->write('INFO', $message);
    }

    public function warning(string $message): void
    {
        $this->write('WARNING', $message);
    }

    public function error(string $message): void
    {
        $this->write('ERROR', $message);
    }

    private function write(string $level, string $message): void
    {
        $timestamp = date('c');
        $line = "[{$timestamp}] {$level}: {$message}\n";
        file_put_contents($this->logFile, $line, FILE_APPEND | LOCK_EX);
    }
}


