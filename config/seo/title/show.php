<?php

return [
    [
        'property' => 'og:url',
        'content' =>  '{{URL.TITLE}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{TITLE.NAME}} ({{TITLE.YEAR}}) - MTDb',
    ],
    [
        'property' => 'og:description',
        'content' => '{{TITLE.DESCRIPTION}}',
    ],
    [
        'property' => 'keywords',
        'content' => 'reviews,photos,user ratings,synopsis,trailers,credits',
    ],
    [
        'property' => 'og:type',
        'content' => 'video.movie',
    ],
    [
        'property' => 'og:image',
        'content' => '{{TITLE.POSTER}}',
    ],
    [
        'property' => 'og:image:width',
        'content' => '300',
    ],
    [
        'property' => 'og:image:height',
        'content' => '450',
    ],
    [
        'nodeName' => 'script',
        'type' => 'application/ld+json',
        '_text' => [
            "@context" => "http://schema.org",
            "@type" => "Movie",
            "@id" => "{{URL.TITLE}}",
            "url" => "{{URL.TITLE}}",
            "name" => "{{TITLE.NAME}}",
            "image" => "{{TITLE.POSTER}}",
            "description" => "{{TITLE.DESCRIPTION}}",
            "genre" => [
                '_type' => 'loop',
                'dataSelector' => 'TITLE.GENRES',
                'template' => '{{TAG.NAME}}'
            ],
            "contentRating" => "{{TITLE.CERTIFICATION}}",
            "actor" => [
                '_type' => 'loop',
                'dataSelector' => 'TITLE.CREDITS',
                'limit' => 10,
                'filter' => [
                    'key' => 'pivot.department',
                    'value' => 'cast',
                ],
                'template' => [
                    "@type" => "Person",
                    "url" => "{{URL.PERSON}}",
                    "name" => "{{PERSON.NAME}}"
                ],
            ],
            "director" => [
                '_type' => 'loop',
                'dataSelector' => 'TITLE.CREDITS',
                'limit' => 3,
                'filter' => [
                    'key' => 'pivot.department',
                    'value' => 'directing',
                ],
                'template' => [
                    "@type" => "Person",
                    "url" => "{{URL.PERSON}}",
                    "name" => "{{PERSON.NAME}}"
                ],
            ],
            "creator" => [
                '_type' => 'loop',
                'dataSelector' => 'TITLE.CREDITS',
                'limit' => 3,
                'filter' => [
                    'key' => 'pivot.department',
                    'value' => 'creators',
                ],
                'template' => [
                    "@type" => "Person",
                    "url" => "{{URL.PERSON}}",
                    "name" => "{{PERSON.NAME}}"
                ],
            ],
            "datePublished" => "{{TITLE.RELEASE_DATE}}",
            "keywords" => [
                '_type' => 'loop',
                'dataSelector' => 'TITLE.KEYWORDS',
                'template' => '{{TAG.NAME}}'
            ],
            "aggregateRating" => [
                "@type" => "AggregateRating",
                "ratingCount" => "{{TITLE.TMDB_VOTE_COUNT}}",
                "bestRating" => "10.0",
                "worstRating" => "1.0",
                "ratingValue" => "{{TITLE.RATING}}"
            ],
            "duration" => "{{TITLE.RUNTIME}}",
        ]
    ]
];