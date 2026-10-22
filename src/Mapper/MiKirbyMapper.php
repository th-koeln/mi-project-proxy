<?php

declare(strict_types=1);

namespace Mi\ProjectProxy\Mapper;

/**
 * Mapper für die MI-Kirby-Quelle
 * (https://www.medieninformatik.th-koeln.de/kirby/abschlussarbeiten).
 *
 * Die Quelle liefert bereits das native Abschlussarbeiten-Schema
 * (siehe thesis-schema.json): ein Array von Objekten mit u. a.
 * firstname/lastname (getrennt), first_supervisor, teaser_image_url,
 * keywords (Array) und visibility.
 *
 * Der Mapper überführt das in dasselbe Normalformat wie der
 * ThesisWorksMapper, damit gemischte Feeds konsistente Felder haben,
 * und ergänzt die reicheren Kirby-Felder (abstract, repository_url, …).
 *
 * Standardmäßig werden nur veröffentlichte Einträge
 * (visibility === "published") ausgegeben.
 */
final class MiKirbyMapper implements MapperInterface
{
    private const BASE_URL = 'https://www.medieninformatik.th-koeln.de';

    public function __construct(private readonly string $sourceName)
    {
    }

    public function map(mixed $payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        // Toleriere sowohl [...] als auch {"projects": [...]} / {"data": [...]}.
        $items = $payload;
        if (isset($payload['projects']) && is_array($payload['projects'])) {
            $items = $payload['projects'];
        } elseif (isset($payload['data']) && is_array($payload['data'])) {
            $items = $payload['data'];
        }

        $projects = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $title = $this->normalizeString($item['title'] ?? null);
            if ($title === null) {
                continue;
            }

            // Unveröffentlichte Einträge nicht ausliefern.
            $visibility = $this->normalizeString($item['visibility'] ?? null);
            if ($visibility !== null && strtolower($visibility) !== 'published') {
                continue;
            }

            $date = $this->normalizeString($item['date'] ?? null);
            $firstExaminer = $this->normalizeString($item['first_supervisor'] ?? null);
            $secondExaminer = $this->normalizeString($item['second_supervisor'] ?? null);
            $author = $this->buildAuthor($item);
            $keywords = $this->normalizeKeywords($item['keywords'] ?? null);

            $thesisUrl = $this->toAbsoluteUrl($this->normalizeString($item['thesis_url'] ?? null));
            $projectUrl = $this->normalizeString($item['project_url'] ?? null);

            $projects[] = [
                'id' => $this->buildId($item, $title, $date),
                'name' => $title,
                'type' => $this->normalizeString($item['type'] ?? null) ?? 'thesis',
                'url' => $projectUrl ?? $thesisUrl,
                'image' => $this->toAbsoluteUrl($this->normalizeString($item['teaser_image_url'] ?? null)),
                'imageCopyright' => $this->normalizeString($item['teaser_image_copyright'] ?? null),
                'updatedAt' => $this->toIsoDateTime($date),
                'source' => $this->sourceName,
                'author' => $author,
                'firstname' => $this->normalizeString($item['firstname'] ?? null),
                'lastname' => $this->normalizeString($item['lastname'] ?? null),
                'status' => $this->normalizeString($item['status'] ?? null),
                'visibility' => $visibility,
                'keywords' => $keywords === [] ? null : implode(', ', $keywords),
                'keywordsList' => $keywords,
                'partner' => $this->normalizeString($item['cooperation_partner'] ?? null),
                'partnerUrl' => $this->normalizeString($item['cooperation_partner_url'] ?? null),
                'firstExaminer' => $firstExaminer,
                'secondExaminer' => $secondExaminer,
                'first_supervisor' => $firstExaminer,
                'second_supervisor' => $secondExaminer,
                'date' => $this->toIsoDate($date),
                'abstract' => $this->normalizeString($item['abstract'] ?? null),
                'thesisUrl' => $thesisUrl,
                'repositoryUrl' => $this->normalizeString($item['repository_url'] ?? null),
                'projectUrl' => $projectUrl,
                'youtubeId' => $this->normalizeString($item['final_presentation_youtube_id'] ?? null),
                'website' => $this->normalizeString($item['personal_website_url'] ?? null),
                'socialMedia' => $this->normalizeStringList($item['personal_social_media_urls'] ?? null),
                'awards' => $this->normalizeStringList($item['awards'] ?? null),
                'raw' => $item,
            ];
        }

        return $projects;
    }

    private function buildAuthor(array $item): ?string
    {
        $firstname = $this->normalizeString($item['firstname'] ?? null);
        $lastname = $this->normalizeString($item['lastname'] ?? null);

        $name = trim(implode(' ', array_filter([$firstname, $lastname])));
        return $name !== '' ? $name : null;
    }

    /** @return list<string> */
    private function normalizeKeywords(mixed $value): array
    {
        return $this->normalizeStringList($value);
    }

    /** @return list<string> */
    private function normalizeStringList(mixed $value): array
    {
        if (is_string($value)) {
            $value = array_map('trim', explode(',', $value));
        }

        if (!is_array($value)) {
            return [];
        }

        $out = [];
        foreach ($value as $entry) {
            $normalized = $this->normalizeString(is_scalar($entry) ? (string) $entry : null);
            if ($normalized !== null) {
                $out[] = $normalized;
            }
        }

        return array_values(array_unique($out));
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

    private function buildId(array $item, string $title, ?string $date): string
    {
        if (isset($item['id']) && trim((string) $item['id']) !== '') {
            return trim((string) $item['id']);
        }

        $firstname = $this->normalizeString($item['firstname'] ?? null) ?? '';
        $lastname = $this->normalizeString($item['lastname'] ?? null) ?? '';
        $slugBase = trim($firstname . ' ' . $lastname);

        if ($slugBase !== '') {
            $slug = $this->slugify($slugBase . ' ' . ($date ?? ''));
            if ($slug !== '') {
                return $slug;
            }
        }

        return sha1($title . '|' . ($date ?? ''));
    }

    private function slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT', $value);
        if ($transliterated !== false) {
            $value = $transliterated;
        }
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        return trim($value, '-');
    }

    private function normalizeString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_scalar($value)) {
            return null;
        }

        $normalized = trim((string) $value);
        return $normalized !== '' ? $normalized : null;
    }
}
