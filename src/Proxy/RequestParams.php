<?php

declare(strict_types=1);

namespace Mi\ProjectProxy\Proxy;

final class RequestParams
{
    /** @param list<string> $sources */
    public function __construct(
        public readonly array $sources,
        public readonly ?string $type,
        public readonly int $limit,
        public readonly bool $includeRaw,
    ) {
    }

    /** @param array<string, mixed> $query */
    public static function fromGlobals(array $query): self
    {
        $sourcesRaw = isset($query['sources']) ? (string) $query['sources'] : 'all';
        $sources = $sourcesRaw === 'all'
            ? ['all']
            : array_values(array_filter(array_map('trim', explode(',', $sourcesRaw))));

        $type = isset($query['type']) ? trim((string) $query['type']) : null;
        if ($type === '') {
            $type = null;
        }

        $limit = isset($query['limit']) ? (int) $query['limit'] : 50;
        if ($limit < 1) {
            $limit = 1;
        }
        if ($limit > 500) {
            $limit = 500;
        }

        $includeRaw = isset($query['raw']) && ((string) $query['raw'] === '1' || (string) $query['raw'] === 'true');

        return new self($sources, $type, $limit, $includeRaw);
    }
}
