<?php

declare(strict_types=1);

namespace Mi\ProjectProxy\Proxy;

use Mi\ProjectProxy\Config;
use Mi\ProjectProxy\Http\HttpClient;
use Mi\ProjectProxy\Http\ResponseCache;
use Mi\ProjectProxy\Mapper\GenericProjectsMapper;
use Mi\ProjectProxy\Mapper\MapperInterface;

final class ProxyService
{
    private const DETAIL_MODES = ['auto', 'internal', 'external'];
    private const DEFAULT_CACHE_TTL = 1800;

    /** @param array<string, array{url: string, mapper: class-string, detail?: string, baseUrl?: string}> $sources */
    public function __construct(
        private readonly array $sources,
        private readonly HttpClient $httpClient,
        private readonly ?ResponseCache $cache = null,
        private readonly int $cacheTtl = self::DEFAULT_CACHE_TTL,
    ) {
    }

    public static function fromDefaultConfig(): self
    {
        $cfg = Config::loadSourcesConfig();
        $ttl = (int) ($cfg['cache']['ttl'] ?? self::DEFAULT_CACHE_TTL);
        $cache = $ttl > 0 ? new ResponseCache(__DIR__ . '/../../var/cache') : null;

        return new self($cfg['sources'], new HttpClient(), $cache, $ttl);
    }

    /** @return array<string, mixed> */
    public function getProjects(RequestParams $params): array
    {
        $selected = $this->selectSources($params->sources);
        $projects = [];
        $errors = [];

        foreach ($selected as $sourceName => $source) {
            try {
                $payload = $this->loadPayload($source['url']);
            } catch (\Throwable $e) {
                $error = [
                    'source' => $sourceName,
                    'url' => $source['url'],
                    'message' => $e->getMessage(),
                ];
                if (!$e instanceof UpstreamException) {
                    $error['type'] = get_class($e);
                }

                // Quelle gerade nicht erreichbar: lieber den letzten Stand zeigen als gar nichts.
                $stale = $this->cache?->get($source['url'], null);
                if ($stale === null) {
                    $errors[] = $error;
                    continue;
                }

                $error['stale'] = true;
                $error['message'] .= sprintf(
                    ' (zwischengespeicherte Daten vom %s werden verwendet)',
                    date(\DateTimeInterface::ATOM, (int) $this->cache->storedAt($source['url'])),
                );
                $errors[] = $error;
                $payload = json_decode($stale, true);
            }

            try {
                $detailMode = $this->detailMode($sourceName, $source['detail'] ?? null);
                $mapper = $this->createMapper($sourceName, $source);
                $mapped = $mapper->map($payload);

                foreach ($mapped as $p) {
                    $projects[] = $this->withDetail($p, $detailMode);
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

    /**
     * Holt das JSON einer Quelle, höchstens alle $cacheTtl Sekunden live.
     * Gespeichert wird nur eine erfolgreiche Antwort mit gültigem JSON.
     */
    private function loadPayload(string $url): mixed
    {
        $cached = $this->cache?->get($url, $this->cacheTtl);
        if ($cached !== null) {
            return json_decode($cached, true);
        }

        $resp = $this->httpClient->get($url);
        if ($resp->status < 200 || $resp->status >= 300) {
            throw new UpstreamException('Upstream returned HTTP ' . $resp->status);
        }

        $payload = json_decode($resp->body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new UpstreamException('Invalid JSON from upstream: ' . json_last_error_msg());
        }

        $this->cache?->set($url, $resp->body);
        return $payload;
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

    private function detailMode(string $sourceName, mixed $configured): string
    {
        $mode = $configured === null ? 'auto' : strtolower(trim((string) $configured));
        if (!in_array($mode, self::DETAIL_MODES, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid detail mode "%s" for source "%s" (expected: %s)',
                (string) $configured,
                $sourceName,
                implode(', ', self::DETAIL_MODES),
            ));
        }

        return $mode;
    }

    /**
     * Löst den Detail-Modus pro Projekt auf. `auto` erscheint nie in der
     * Ausgabe: mit Detailseiten-URL wird verlinkt, sonst intern gerendert.
     *
     * @param array<string, mixed> $project
     * @return array<string, mixed>
     */
    private function withDetail(array $project, string $mode): array
    {
        $detailUrl = $project['detailUrl'] ?? null;
        $detailUrl = is_string($detailUrl) && trim($detailUrl) !== '' ? trim($detailUrl) : null;
        unset($project['detailUrl']);

        if ($mode === 'auto') {
            $mode = $detailUrl !== null ? 'external' : 'internal';
        }

        // external ohne Ziel wäre ein toter Link → intern rendern.
        if ($mode === 'external' && $detailUrl === null) {
            $mode = 'internal';
        }

        $project['detail'] = [
            'mode' => $mode,
            'url' => $detailUrl,
        ];

        return $project;
    }

    /** @param array{mapper?: class-string, baseUrl?: string} $source */
    private function createMapper(string $sourceName, array $source): MapperInterface
    {
        $mapperClass = $source['mapper'] ?? null;
        if ($mapperClass === null || !class_exists($mapperClass)) {
            return new GenericProjectsMapper($sourceName);
        }

        // Mapper-Klassen bekommen den Source-Namen und die optionale baseUrl im Konstruktor.
        return new $mapperClass($sourceName, $source['baseUrl'] ?? null);
    }
}
