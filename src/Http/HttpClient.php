<?php

declare(strict_types=1);

namespace Mi\ProjectProxy\Http;

final class HttpClient
{
    public function __construct(
        private readonly int $timeoutSeconds = 10,
        private readonly string $userAgent = 'mi-project-proxy/1.0',
    ) {
    }

    public function get(string $url): HttpResponse
    {
        $ch = curl_init($url);
        if ($ch === false) {
            throw new \RuntimeException('Failed to init curl');
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_CONNECTTIMEOUT => $this->timeoutSeconds,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_USERAGENT => $this->userAgent,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
            ],
            CURLOPT_HEADER => true,
        ]);

        $raw = curl_exec($ch);
        if ($raw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('HTTP request failed: ' . $err);
        }

        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $headerRaw = substr($raw, 0, $headerSize);
        $body = substr($raw, $headerSize);

        return new HttpResponse($status, $body, self::parseHeaders($headerRaw));
    }

    /** @return array<string, string> */
    private static function parseHeaders(string $rawHeaders): array
    {
        $headers = [];
        $lines = preg_split('/\r\n|\n|\r/', trim($rawHeaders)) ?: [];
        foreach ($lines as $line) {
            if (str_contains($line, ':')) {
                [$k, $v] = explode(':', $line, 2);
                $headers[trim($k)] = trim($v);
            }
        }
        return $headers;
    }
}
