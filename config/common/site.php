<?php

return [
    'version' => env('APP_VERSION'),
    'demo'    => env('IS_DEMO_SITE', false),
    'disable_update_auth' => env('DISABLE_UPDATE_AUTH', false),
    'use_symlinks' => env('USE_SYMLINKS', false),
    'billing_enabled' => env('BILLING_ENABLED', false),
    'enable_contact_page' => env('ENABLE_CONTACT_PAGE', true),
    'rating_column' => env('RATING_COLUMN', 'tmdb_vote_average'),
];