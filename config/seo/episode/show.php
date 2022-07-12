<?php

return [
    [
        'property' => 'og:url',
        'content' =>  '{{URL.EPISODE}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{EPISODE.TITLE.NAME}} ({{EPISODE.TITLE.YEAR}}) - {{EPISODE.NAME}} - {{SITE_NAME}}',
    ],
    [
        'property' => 'og:description',
        'content' => '{{EPISODE.DESCRIPTION}}',
    ],
    [
        'property' => 'keywords',
        'content' => 'reviews,photos,user ratings,synopsis,trailers,credits',
    ],
    [
        'nodeName' => 'script',
        'type' => 'application/ld+json',
        '_text' => [
            '@context' => 'http://schema.org',
            '@type' => 'TVEpisode',
            '@id' => '{{URL.EPISODE}}',
            'url' => '{{URL.EPISODE}}',
            'name' => '{{EPISODE.NAME}}',
            'image' => '{{EPISODE.POSTER}}',
            'timeRequired' => '{{EPISODE.TITLE.RUNTIME}}',
            'contentRating' => 'TV-PG',
            'description' => '{{EPISODE.DESCRIPTION}}',
            'datePublished' => '{{EPISODE.RELEASE_DATE}}',
            "keywords" => [
                '_type' => 'loop',
                'dataSelector' => 'EPISODE.TITLE.KEYWORDS',
                'template' => '{{TAG.NAME}}'
            ],
            'genre' => [
                '_type' => 'loop',
                'dataSelector' => 'EPISODE.TITLE.GENRES',
                'template' => '{{TAG.NAME}}'
            ],
            'actor' => [
                '_type' => 'loop',
                'dataSelector' => 'EPISODE.CREDITS',
                'limit' => 10,
                'filter' => [
                    'key' => 'pivot.department',
                    'value' => 'cast',
                ],
                'template' => [
                    '@type' => 'Person',
                    'url' => '{{URL.PERSON}}',
                    'name' => '{{PERSON.NAME}}'
                ],
            ],
            'director' => [
                '_type' => 'loop',
                'dataSelector' => 'EPISODE.CREDITS',
                'limit' => 3,
                'filter' => [
                    'key' => 'pivot.department',
                    'value' => 'directing',
                ],
                'template' => [
                    '@type' => 'Person',
                    'url' => '{{URL.PERSON}}',
                    'name' => '{{PERSON.NAME}}'
                ],
            ],
            'creator' => [
                '_type' => 'loop',
                'dataSelector' => 'TITLE.CREDITS',
                'limit' => 3,
                'filter' => [
                    'key' => 'pivot.department',
                    'value' => 'creators',
                ],
                'template' => [
                    '@type' => 'Person',
                    'url' => '{{URL.PERSON}}',
                    'name' => '{{PERSON.NAME}}'
                ],
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingCount' => '{{EPISODE.TMDB_VOTE_COUNT}}',
                'bestRating' => '10.0',
                'worstRating' => '1.0',
                'ratingValue' => '{{EPISODE.RATING}}'
            ],
        ]
    ]
];