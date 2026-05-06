<?php

declare(strict_types=1);

namespace Mi\ProjectProxy;

final class Config
{
    /** @return array{sources: array<string, array{url: string, mapper: class-string}>} */
    public static function loadSourcesConfig(): array
    {
        $configFile = __DIR__ . '/../config/sources.php';
        $config = require $configFile;

        if (!is_array($config) || !isset($config['sources']) || !is_array($config['sources'])) {
            throw new \RuntimeException('Invalid sources config: expected ["sources" => ...]');
        }

        return $config;
    }
}
