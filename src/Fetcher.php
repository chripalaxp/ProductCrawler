<?php

namespace MiniCrawler;

use RuntimeException;

class Fetcher
{
    public function fetch(string $url, int $timeoutSeconds = 20): string
    {
        // Try once, then retry once after a brief pause
        try {
            return $this->performRequest($url, $timeoutSeconds);
        } catch (\Throwable $e) {
            sleep(1);
            return $this->performRequest($url, $timeoutSeconds);
        }
    }

    private function performRequest(string $url, int $timeoutSeconds): string
    {
        $ch = curl_init();
        if ($ch === false) {
            throw new RuntimeException('Failed to initialize cURL');
        }
        $headers = [
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.9',
            'Cache-Control: no-cache'
        ];
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => $timeoutSeconds,
            CURLOPT_TIMEOUT => $timeoutSeconds,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
            CURLOPT_HTTPHEADER => $headers,
            
            // SSL verification enabled with CA bundle
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO => __DIR__ . '/../cacert.pem',
            
            CURLOPT_ENCODING => ''
        ]);
        $response = curl_exec($ch);
        if ($response === false) {
            $err = curl_error($ch);
            $code = curl_errno($ch);
            curl_close($ch);
            throw new RuntimeException('cURL error (' . $code . '): ' . $err);
        }
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($status < 200 || $status >= 300) {
            throw new RuntimeException('HTTP status ' . $status . ' for ' . $url);
        }
        return (string)$response;
    }
}


