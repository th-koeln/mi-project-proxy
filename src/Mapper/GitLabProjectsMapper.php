<?php

declare(strict_types=1);

namespace Mi\ProjectProxy\Mapper;

/**
 * Mapper für GitLab-ähnliche Project-List Responses (typisch: root ist eine Liste).
 */
final class GitLabProjectsMapper implements MapperInterface
{
    public function __construct(private readonly string $sourceName)
    {
    }

    public function map(mixed $payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        $projects = [];
        foreach ($payload as $item) {
            if (!is_array($item)) {
                continue;
            }

            $name = $item['path_with_namespace'] ?? $item['name_with_namespace'] ?? $item['name'] ?? null;
            if ($name === null) {
                continue;
            }

            $projects[] = [
                'id' => $item['id'] ?? null,
                'name' => (string) $name,
                'type' => 'gitlab',
                'url' => $item['web_url'] ?? null,
                'updatedAt' => $item['last_activity_at'] ?? $item['updated_at'] ?? null,
                'source' => $this->sourceName,
                'raw' => $item,
            ];
        }

        return $projects;
    }
}
