<?php

return [
    [
        'property' => 'og:url',
        'content' =>  '{{URL.SEASON}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{TITLE.NAME}} ({{TITLE.YEAR}}) - Season {{TITLE.SEASON.NUMBER}} - {{SITE_NAME}}',
    ],
    [
        'property' => 'og:description',
        'content' => 'List of episodes for {{TITLE.NAME}}: Season {{TITLE.SEASON.NUMBER}}',
    ],
    [
        'property' => 'keywords',
        'content' => 'reviews,photos,user ratings,synopsis,trailers,credits',
    ],
];