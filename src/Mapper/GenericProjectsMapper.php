<?php

declare(strict_types=1);

namespace Mi\ProjectProxy\Mapper;

/**
 * Sehr einfacher Fallback-Mapper:
 * - akzeptiert entweder {"projects": [...]}, oder direkt [...]
 * - versucht ein paar gängige Felder zu normalisieren
 */
final class GenericProjectsMapper implements MapperInterface
{
    public function __construct(private readonly string $sourceName)
    {
    }

    public function map(mixed $payload): array
    {
        $items = [];

        if (is_array($payload)) {
            $items = isset($payload['projects']) && is_array($payload['projects'])
                ? $payload['projects']
                : $payload;
        } elseif (is_object($payload) && isset($payload->projects) && is_array($payload->projects)) {
            $items = $payload->projects;
        }

        $projects = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $id = $item['id'] ?? $item['project_id'] ?? $item['uuid'] ?? null;
            $name = $item['name'] ?? $item['title'] ?? $item['path_with_namespace'] ?? null;
            $type = $item['type'] ?? $item['project_type'] ?? null;
            $url = $item['web_url'] ?? $item['url'] ?? $item['http_url_to_repo'] ?? null;
            $updatedAt = $item['updated_at'] ?? $item['last_activity_at'] ?? $item['modified'] ?? null;

            if ($name === null) {
                continue;
            }

            $projects[] = [
                'id' => $id,
                'name' => (string) $name,
                'type' => $type !== null ? (string) $type : null,
                'url' => $url !== null ? (string) $url : null,
                'updatedAt' => $updatedAt !== null ? (string) $updatedAt : null,
                'source' => $this->sourceName,
                'raw' => $item,
            ];
        }

        return $projects;
    }
}
