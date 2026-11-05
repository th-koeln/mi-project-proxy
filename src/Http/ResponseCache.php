<?php

declare(strict_types=1);

namespace Mi\ProjectProxy\Http;

/**
 * Dateibasierter Zwischenspeicher für Upstream-Antworten (eine Datei pro URL).
 *
 * Best effort: Ist das Verzeichnis nicht beschreibbar, wird einfach nichts
 * gespeichert und der Proxy fragt die Quellen bei jeder Anfrage live ab.
 */
final class ResponseCache
{
    public function __construct(private readonly string $dir)
    {
    }

    /** Liefert den gespeicherten Body, wenn er höchstens $maxAge Sekunden alt ist (null = beliebig alt). */
    public function get(string $url, ?int $maxAge): ?string
    {
        $file = $this->path($url);
        $mtime = @filemtime($file);
        if ($mtime === false) {
            return null;
        }

        if ($maxAge !== null && time() - $mtime > $maxAge) {
            return null;
        }

        $body = @file_get_contents($file);
        return $body === false ? null : $body;
    }

    /** Zeitpunkt der letzten Speicherung als Unix-Timestamp, sonst null. */
    public function storedAt(string $url): ?int
    {
        $mtime = @filemtime($this->path($url));
        return $mtime === false ? null : $mtime;
    }

    public function set(string $url, string $body): void
    {
        if (!is_dir($this->dir) && !@mkdir($this->dir, 0775, true) && !is_dir($this->dir)) {
            return;
        }

        // Erst in eine temporäre Datei schreiben, dann umbenennen: parallele Anfragen lesen nie eine halbe Datei.
        $file = $this->path($url);
        $tmp = $file . '.' . bin2hex(random_bytes(4)) . '.tmp';
        if (@file_put_contents($tmp, $body) === false || !@rename($tmp, $file)) {
            @unlink($tmp);
        }
    }

    private function path(string $url): string
    {
        return $this->dir . '/' . sha1($url) . '.json';
    }
}
