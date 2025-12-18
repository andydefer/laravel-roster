<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Timezone
    |--------------------------------------------------------------------------
    |
    | The default timezone to use for all datetime operations.
    |
    */
    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Default Durations
    |--------------------------------------------------------------------------
    |
    | Default duration settings for various operations.
    |
    */
    'durations' => [
        'minimum_impediment_minutes' => 5,
        'minimum_schedule_minutes' => 15,
        'default_slot_duration_minutes' => 60,
        'default_slot_interval_minutes' => 30,
        'max_days_to_check' => 365,
        'search_days_ahead' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Availability Settings
    |--------------------------------------------------------------------------
    |
    | Settings related to availability management.
    |
    */
    'availability' => [
        'auto_merge_adjacent' => true,
        'allow_overlap' => false,
        'validate_future_dates' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Settings
    |--------------------------------------------------------------------------
    |
    | Database-specific settings for the package.
    |
    */
    'database' => [
        'use_json_constraints' => env('DB_CONNECTION') === 'pgsql',
        'check_constraints' => env('DB_CONNECTION') === 'mysql',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Cache settings for performance optimization.
    |
    */
    'cache' => [
        'enabled' => env('ROSTER_CACHE_ENABLED', true),
        'ttl' => env('ROSTER_CACHE_TTL', 3600), // seconds
        'prefix' => 'roster_',
    ],
];
