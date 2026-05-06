<?php

declare(strict_types=1);

namespace Mi\ProjectProxy\Proxy;

use Mi\ProjectProxy\Config;
use Mi\ProjectProxy\Http\HttpClient;
use Mi\ProjectProxy\Mapper\GenericProjectsMapper;
use Mi\ProjectProxy\Mapper\MapperInterface;

final class ProxyService
{
    /** @param array<string, array{url: string, mapper: class-string}> $sources */
    public function __construct(
        private readonly array $sources,
        private readonly HttpClient $httpClient,
    ) {
    }

    public static function fromDefaultConfig(): self
    {
        $cfg = Config::loadSourcesConfig();
        return new self($cfg['sources'], new HttpClient());
    }

    /** @return array<string, mixed> */
    public function getProjects(RequestParams $params): array
    {
        $selected = $this->selectSources($params->sources);
        $projects = [];
        $errors = [];

        foreach ($selected as $sourceName => $source) {
            try {
                $resp = $this->httpClient->get($source['url']);
                if ($resp->status < 200 || $resp->status >= 300) {
                    $errors[] = [
                        'source' => $sourceName,
                        'url' => $source['url'],
                        'message' => 'Upstream returned HTTP ' . $resp->status,
                    ];
                    continue;
                }

                $payload = json_decode($resp->body, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $errors[] = [
                        'source' => $sourceName,
                        'url' => $source['url'],
                        'message' => 'Invalid JSON from upstream: ' . json_last_error_msg(),
                    ];
                    continue;
                }

                $mapper = $this->createMapper($sourceName, $source['mapper'] ?? null);
                $mapped = $mapper->map($payload);

                foreach ($mapped as $p) {
                    $projects[] = $p;
                }
            } catch (\Throwable $e) {
                $errors[] = [
                    'source' => $sourceName,
                    'url' => $source['url'],
                    'message' => $e->getMessage(),
                    'type' => get_class($e),
                ];
            }
        }

        if ($params->type !== null) {
            $projects = array_values(array_filter($projects, static function (array $p) use ($params): bool {
                $t = $p['type'] ?? null;
                return $t !== null && mb_strtolower((string) $t) === mb_strtolower($params->type);
            }));
        }

        if (!$params->includeRaw) {
            $projects = array_map(static function (array $p): array {
                unset($p['raw']);
                return $p;
            }, $projects);
        }

        $projects = array_slice($projects, 0, $params->limit);

        return [
            'meta' => [
                'ok' => count($errors) === 0,
                'count' => count($projects),
                'limit' => $params->limit,
                'type' => $params->type,
                'sources' => array_keys($selected),
            ],
            'projects' => $projects,
            'errors' => $errors,
        ];
    }

    /** @param list<string> $wanted */
    private function selectSources(array $wanted): array
    {
        if ($wanted === ['all']) {
            return $this->sources;
        }

        $selected = [];
        foreach ($wanted as $name) {
            if (isset($this->sources[$name])) {
                $selected[$name] = $this->sources[$name];
            }
        }

        return $selected;
    }

    private function createMapper(string $sourceName, ?string $mapperClass): MapperInterface
    {
        if ($mapperClass === null || !class_exists($mapperClass)) {
            return new GenericProjectsMapper($sourceName);
        }

        // Mapper-Klassen bekommen den Source-Namen im Konstruktor.
        return new $mapperClass($sourceName);
    }
}
