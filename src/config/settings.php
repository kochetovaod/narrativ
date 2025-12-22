<?php

return [
    'auto_discover_settings' => [
        app_path('Settings'),
    ],

    'migrations_paths' => [
        database_path('settings'),
    ],

    'settings' => [
        App\Settings\ContactSettings::class,
        App\Settings\AboutSettings::class,
        App\Settings\BrandSettings::class,
        App\Settings\SeoSettings::class,
    ],
];
