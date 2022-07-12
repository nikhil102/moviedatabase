<?php

return [
    'roles' => [
        'users' => [
            'titles.view',
            'people.view',
            'reviews.view',
            'reviews.create',
            'news.view',
            'lists.create',
            'captions.view',
            'plans.view',
        ],
        'guests' => [
            'titles.view',
            'people.view',
            'reviews.view',
            'news.view',
            'captions.view',
            'plans.view',
        ]
    ],
    'all' => [
        'titles' => [
            'titles.view',
            'titles.create',
            'titles.update',
            'titles.delete',
        ],
        'reviews' => [
            'reviews.view',
            'reviews.create',
            'reviews.update',
            'reviews.delete',
        ],
        'people' => [
            'people.view',
            'people.create',
            'people.update',
            'people.delete',
        ],
        'news' => [
            'news.view',
            'news.create',
            'news.update',
            'news.delete',
        ],
        'videos' => [
            [
                'name' => 'videos.rate',
                'description' => 'Allow rating videos.',
            ],
            'videos.view',
            'videos.create',
            'videos.update',
            'videos.delete',
        ],
        'lists' => [
            'lists.view',
            'lists.create',
            'lists.update',
            'lists.delete',
        ],
        'captions' => [
            'captions.view',
            'captions.create',
            'captions.update',
            'captions.delete',
        ],
    ]
];
