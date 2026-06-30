<?php

declare(strict_types=1);

namespace Mi\ProjectProxy\Mapper;

/**
 * Mapper für https://cnoss.github.io/thesis/works.json.
 */
final class ThesisWorksMapper implements MapperInterface
{
    private const BASE_URL = 'https://cnoss.github.io/thesis';

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

            $title = isset($item['title']) ? trim((string) $item['title']) : '';
            if ($title === '') {
                continue;
            }

            $date = isset($item['date']) ? trim((string) $item['date']) : null;
            $relativeUrl = isset($item['url']) ? trim((string) $item['url']) : null;
            $absoluteUrl = $this->toAbsoluteUrl($relativeUrl);
            $image = isset($item['image']) ? trim((string) $item['image']) : null;
            $firstExaminer = $this->normalizeString(
                $item['firstExaminer'] ?? $item['firstSupervisor'] ?? $item['first_supervisor'] ?? null
            );
            $secondExaminer = $this->normalizeString(
                $item['secondExaminer'] ?? $item['secondSupervisor'] ?? $item['second_supervisor'] ?? null
            );

            $projects[] = [
                'id' => $this->buildId($item, $title, $relativeUrl),
                'name' => $title,
                'type' => isset($item['type']) && trim((string) $item['type']) !== ''
                    ? trim((string) $item['type'])
                    : 'thesis',
                'url' => $absoluteUrl,
                'image' => $this->toAbsoluteUrl($image),
                'updatedAt' => $this->toIsoDateTime($date),
                'source' => $this->sourceName,
                'author' => isset($item['author']) && trim((string) $item['author']) !== ''
                    ? trim((string) $item['author'])
                    : null,
                'status' => isset($item['status']) && trim((string) $item['status']) !== ''
                    ? trim((string) $item['status'])
                    : null,
                'keywords' => isset($item['keywords']) && trim((string) $item['keywords']) !== ''
                    ? trim((string) $item['keywords'])
                    : null,
                'partner' => isset($item['partner']) && trim((string) $item['partner']) !== ''
                    ? trim((string) $item['partner'])
                    : null,
                'partnerUrl' => isset($item['partnerUrl']) && trim((string) $item['partnerUrl']) !== ''
                    ? trim((string) $item['partnerUrl'])
                    : null,
                'firstExaminer' => $firstExaminer,
                'secondExaminer' => $secondExaminer,
                'first_supervisor' => $firstExaminer,
                'second_supervisor' => $secondExaminer,
                'date' => $this->toIsoDate($date),
                'raw' => $item,
            ];
        }

        return $projects;
    }

    private function toAbsoluteUrl(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $url) === 1) {
            return $url;
        }

        if ($url[0] === '/') {
            return self::BASE_URL . $url;
        }

        return self::BASE_URL . '/' . $url;
    }

    private function toIsoDate(?string $date): ?string
    {
        if ($date === null || $date === '') {
            return null;
        }

        try {
            return (new \DateTimeImmutable($date))->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function toIsoDateTime(?string $date): ?string
    {
        if ($date === null || $date === '') {
            return null;
        }

        try {
            return (new \DateTimeImmutable($date))->format(\DateTimeInterface::ATOM);
        } catch (\Throwable) {
            return null;
        }
    }

    private function buildId(array $item, string $title, ?string $relativeUrl): string
    {
        if (isset($item['id']) && trim((string) $item['id']) !== '') {
            return trim((string) $item['id']);
        }

        if ($relativeUrl !== null && $relativeUrl !== '') {
            return ltrim($relativeUrl, '/');
        }

        return sha1($title . '|' . ($item['date'] ?? ''));
    }

    private function normalizeString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);
        return $normalized !== '' ? $normalized : null;
    }
}