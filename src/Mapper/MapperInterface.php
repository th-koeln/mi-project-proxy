<?php

declare(strict_types=1);

namespace Mi\ProjectProxy\Mapper;

interface MapperInterface
{
    /**
     * @param mixed $payload Decoded JSON (array/object/scalar)
     * @return list<array<string, mixed>> Normalized projects
     */
    public function map(mixed $payload): array;
}
