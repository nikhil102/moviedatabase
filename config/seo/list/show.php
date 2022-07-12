<?php

return [
    [
        'property' => 'og:url',
        'content' =>  '{{URL.LIST_MODEL}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{LIST.NAME}} - {{SITE_NAME}}',
    ],
    [
        'property' => 'og:description',
        'content' => '{{LIST.DESCRIPTION}}',
    ],
    [
        'property' => 'keywords',
        'content' => 'movies, films, movie database, actors, actresses, directors, stars, synopsis, trailers, credits, cast',
    ],
    [
        'nodeName' => 'script',
        'type' => 'application/ld+json',
        '_text' => [
            '@context' => 'http://schema.org',
            '@id' => '{{URL.LIST_MODEL}}',
            'url' => '{{URL.LIST_MODEL}}',

            "@type" => "CreativeWork",
            "dateModified" => "{{LIST.UPDATED_AT}}",
            "name" => "{{LIST.NAME}}",
            "about" => [
                "@type" => "ItemList",
                "itemListElement" =>  [
                    '_type' => 'loop',
                    'dataSelector' => 'ITEMS',
                    'limit' => 30,
                    'template' => [
                        '@type' => 'ListItem',
                        'position' => '{{TITLE.PIVOT.ORDER}}',
                        "url" => "{{URL.MEDIA_ITEM}}"
                    ]
                ],
            ],
        ]
    ]
];