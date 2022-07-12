<?php

return [
    [
        'property' => 'og:url',
        'content' =>  '{{URL.ARTICLE}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{ARTICLE.TITLE}} - {{SITE_NAME}}',
    ],
    [
        'property' => 'og:description',
        'content' => 'The Movie Database ({{SITE_NAME}}) is a popular database for movies, TV shows and celebrities.',
    ],
    [
        'property' => 'keywords',
        'content' => 'movies, films, movie database, actors, actresses, directors, stars, synopsis, trailers, credits, cast',
    ],
    [
        'property' => 'og:type',
        'content' => 'video.movie',
    ],
    [
        'property' => 'og:image',
        'content' => '{{ARTICLE.META.IMAGE}}',
    ],
    [
        'property' => 'og:image:width',
        'content' => '270',
    ],
    [
        'property' => 'og:image:height',
        'content' => '400',
    ],
];