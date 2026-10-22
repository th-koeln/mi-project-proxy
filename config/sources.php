<?php

declare(strict_types=1);

use Mi\ProjectProxy\Mapper\GenericProjectsMapper;
use Mi\ProjectProxy\Mapper\GitLabProjectsMapper;
use Mi\ProjectProxy\Mapper\MiKirbyMapper;
use Mi\ProjectProxy\Mapper\ThesisWorksMapper;

/**
 * Trage hier deine Ressourcen ein.
 * Jede Resource bekommt:
 * - url: Endpoint, der JSON liefert
 * - mapper: Klasse, die das JSON in das Normalformat mappt
 * - detail: wo die Detailseite gerendert wird (optional, Standard: auto)
 *     auto     → mit Detailseiten-URL extern verlinken, sonst intern rendern
 *     internal → immer auf dem MI-Server rendern
 *     external → immer auf die Quelle verlinken (ohne URL: intern)
 */
return [
    'sources' => [
        'thesis-works' => [
            'url' => 'https://cnoss.github.io/thesis/works.json',
            'mapper' => ThesisWorksMapper::class,
            'detail' => 'auto',
        ],
        'mi-kirby' => [
            'url' => 'https://www.medieninformatik.th-koeln.de/kirby/abschlussarbeiten',
            'mapper' => MiKirbyMapper::class,
            'detail' => 'auto',
        ],

        // Beispiel 1: GitLab API (typisch root = Liste)
        // 'gitlab-main' => [
        //     'url' => 'https://gitlab.example.com/api/v4/projects?simple=true&per_page=100',
        //     'mapper' => GitLabProjectsMapper::class,
        // ],

        // Beispiel 2: generischer JSON-Endpoint
        // akzeptiert entweder {"projects": [...]} oder direkt [...]
        // 'legacy-system' => [
        //     'url' => 'https://legacy.example.com/projects.json',
        //     'mapper' => GenericProjectsMapper::class,
        // ],
    ],
];
