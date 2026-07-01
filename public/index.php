<?php

declare(strict_types=1);

use Mi\ProjectProxy\Proxy\ProxyService;
use Mi\ProjectProxy\Proxy\RequestParams;
use Mi\ProjectProxy\Proxy\ResponseEmitter;

require __DIR__ . '/../src/Bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    \Mi\ProjectProxy\Proxy\ResponseEmitter::options();
    exit;
}

$params = RequestParams::fromGlobals($_GET);
$service = ProxyService::fromDefaultConfig();

try {
    $result = $service->getProjects($params);
    ResponseEmitter::json(200, $result);
} catch (Throwable $e) {
    ResponseEmitter::json(500, [
        'meta' => [
            'ok' => false,
            'error' => 'internal_error',
        ],
        'projects' => [],
        'errors' => [[
            'message' => $e->getMessage(),
            'type' => get_class($e),
        ]],
    ]);
}
