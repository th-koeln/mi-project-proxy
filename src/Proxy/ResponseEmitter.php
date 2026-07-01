<?php

declare(strict_types=1);

namespace Mi\ProjectProxy\Proxy;

final class ResponseEmitter
{
    /** @param array<string, mixed> $payload */
    public static function json(int $status, array $payload): void
    {
        self::sendCorsHeaders();
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    public static function options(): void
    {
        self::sendCorsHeaders();
        http_response_code(204);
    }

    private static function sendCorsHeaders(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 600');
        header('Vary: Origin');
    }
}
