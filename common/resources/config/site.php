<?php

return [
    'version' => env('APP_VERSION'),
    'uploads_disk' => env('UPLOADS_DISK', 'uploads_local'),
    'demo'    => env('IS_DEMO_SITE', false),
    'disable_update_auth' => env('DISABLE_UPDATE_AUTH', false),
    'use_symlinks' => env('USE_SYMLINKS', false),
    'enable_contact_page' => env('ENABLE_CONTACT_PAGE', false),
    'billing_integrated' => env('BILLING_ENABLED', false),
    'notifications_integrated' => env('NOTIFICATIONS_ENABLED', false),
    'api_integrated' => env('API_INTEGRATED', false),
    'enable_custom_domains' => env('ENABLE_CUSTOM_DOMAINS', false),
];