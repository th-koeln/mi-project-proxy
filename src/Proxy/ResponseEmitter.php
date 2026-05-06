<?php

declare(strict_types=1);

namespace Mi\ProjectProxy\Proxy;

final class ResponseEmitter
{
    /** @param array<string, mixed> $payload */
    public static function json(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    }
}
