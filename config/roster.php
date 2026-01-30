<?php

declare(strict_types=1);

/**
 * Configuration file for the Roster package.
 *
 * This configuration provides unified settings for availability, scheduling,
 * and impediment management within the Roster package.
 * All configuration values can be overridden via environment variables.
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Core Settings
    |--------------------------------------------------------------------------
    */

    'timezone' => env('ROSTER_TIMEZONE', env('APP_TIMEZONE', 'UTC')),

    'allow_middleware' => true,

    'default_timezone' => 'UTC',

    'allowed_types' => [],

    /*
    |--------------------------------------------------------------------------
    | Duration Constraints
    |--------------------------------------------------------------------------
    |
    | Controls the time intervals and search periods for roster scheduling.
    |
    */

    'durations' => [
        'default_slot_interval_minutes' => env('ROSTER_DEFAULT_SLOT_INTERVAL', 15),
        'max_search_period_days' => env('ROSTER_MAX_SEARCH_PERIOD_DAYS', 365),
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Directories containing custom validation rules for roster operations.
    |
    */

    'rule_directories' => [],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Database-specific settings including constraint handling for different
    | database systems.
    |
    */

    'database' => [
        'use_postgres_exclusion_constraints' => env(
            'ROSTER_DB_USE_POSTGRES_EXCLUSION_CONSTRAINTS',
            env('DB_CONNECTION') === 'pgsql'
        ),
        'check_constraints' => env(
            'ROSTER_DB_CHECK_CONSTRAINTS',
            env('DB_CONNECTION') === 'mysql'
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for caching roster rules and related data to improve performance.
    |
    */

    'cache' => [
        'use_file_cache' => env('ROSTER_USE_FILE_CACHE', true),
        'cache_file' => storage_path('framework/cache/roster_rules.php'),
        'cache_max_age_hours' => env('ROSTER_CACHE_MAX_AGE', 24),
        'always_cache_in_production' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Reconciliation Settings
    |--------------------------------------------------------------------------
    |
    | Determines how the system handles days outside validity periods during updates.
    | When enabled, warnings will be triggered; when disabled, days are silently reconciled.
    |
    */

    'reconciliation_warning' => env('ROSTER_RECONCILIATION_WARNING', false),
];
