<?php

return [
    'roles' => [
        'users' => [
            'users.view',
            'localizations.view',
            'pages.view',
            'files.create',
            'plans.view',
        ],

        'guests' => [
            'users.view',
            'custom_pages.view',
            'plans.view',
        ],
    ],
    'all' => [
        'admin' => [
            [
                'name' => 'admin.access',
                'description' => 'Required in order to access any admin area page.',
            ],
            [
                'name' => 'appearance.update',
                'description' => 'Allows access to appearance editor.'
            ]
        ],

        'roles' => [
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
        ],

        'analytics' => [
            [
                'name' => 'reports.view',
                'description' => 'Allows access to analytics page.',
            ]
        ],

        'custom_pages' => [
            'custom_pages.view',
            [
                'name' => 'custom_pages.create',
                'restrictions' => [
                    [
                        'name' => 'count',
                        'type' => 'number',
                        'description' => __('policies.count_description', ['resources' => 'pages'])
                    ]
                ]
            ],
            'custom_pages.update',
            'custom_pages.delete',
        ],

        'custom_domains' => [
            'custom_domains.view',
            [
                'name' => 'custom_domains.create',
                'restrictions' => [
                    [
                        'name' => 'count',
                        'type' => 'number',
                        'description' => __('policies.count_description', ['resources' => 'domains'])
                    ]
                ]
            ],
            'custom_domains.update',
            'custom_domains.delete',
        ],

        'files' => [
            'files.view',
            'files.create',
            'files.delete',
        ],

        'users' => [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
        ],

        'localizations' => [
            'localizations.view',
            'localizations.create',
            'localizations.update',
            'localizations.delete',
        ],

        'mail_templates' => [
            'mail_templates.view',
            'mail_templates.update',
        ],

        'settings' => [
            'settings.view',
            'settings.update',
        ],

        'plans' => [
            'plans.view',
            'plans.create',
            'plans.create',
            'plans.delete',
        ],
    ]
];
