<?php

declare(strict_types=1);

namespace Mi\ProjectProxy\Http;

final class HttpResponse
{
    public function __construct(
        public readonly int $status,
        public readonly string $body,
        /** @var array<string, string> */
        public readonly array $headers = [],
    ) {
    }
}
