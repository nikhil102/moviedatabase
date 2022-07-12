<?php

return [
    [
        'property' => 'og:url',
        'content' =>  '{{URL.PERSON}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{PERSON.NAME}} - {{SITE_NAME}}',
    ],
    [
        'property' => 'og:description',
        'content' => '{{PERSON.DESCRIPTION}}',
    ],
    [
        'property' => 'keywords',
        'content' => 'biography, facts, photos, credits',
    ],
    [
        'property' => 'og:type',
        'content' => 'profile',
    ],
    [
        'property' => 'og:image',
        'content' => '{{PERSON.POSTER}}',
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
            "@type" => "Person",
            "@id" => "{{URL.PERSON}}",
            "url" => "{{URL.PERSON}}",
            "name" => "{{PERSON.NAME}}",
            "image" => "{{PERSON.POSTER}}",
            "description" => "{{PERSON.DESCRIPTION}}",
            "jobTitle" => [
                "{{PERSON.KNOWN_FOR}}",
            ],
        ]
    ]
];