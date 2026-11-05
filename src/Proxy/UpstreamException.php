<?php

declare(strict_types=1);

namespace Mi\ProjectProxy\Proxy;

/** Die Quelle hat geantwortet, aber nicht mit HTTP 2xx und gültigem JSON. */
final class UpstreamException extends \RuntimeException
{
}
