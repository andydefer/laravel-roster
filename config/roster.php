<?php

declare(strict_types=1);

/**
 * Configuration file for the Roster package.
 *
 * Provides unified configuration for availability, scheduling, and impediment management.
 * All settings can be overridden via environment variables.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Core & Timezone
    |--------------------------------------------------------------------------
    */
    'timezone' =>  env('ROSTER_TIMEZONE', env('APP_TIMEZONE', 'UTC')),

    'allow_middleware' => true,

    'default_timezone' => 'UTC',

    'allowed_types' => [],

    /*
    |--------------------------------------------------------------------------
    | Duration Constraints
    |--------------------------------------------------------------------------
    */
    'durations' => [
        'default_slot_interval_minutes' => env('ROSTER_DEFAULT_SLOT_INTERVAL', 30),

        'max_search_period_days' => env('ROSTER_MAX_SEARCH_PERIOD_DAYS', 365),
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    */
    'rule_directories' => [],

    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
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
    | Cache
    |--------------------------------------------------------------------------
    */
    'cache' => [

        'use_file_cache' => env('ROSTER_USE_FILE_CACHE', true),
        'cache_file' => storage_path('framework/cache/roster_rules.php'),
        'cache_max_age_hours' => env('ROSTER_CACHE_MAX_AGE', 24),
        'always_cache_in_production' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Reconciliation
    |--------------------------------------------------------------------------
    |
    | If true, when days are outside the validity period during an update, a
    | PHP warning will be triggered. If false, the days are silently reconciled.
    |
    */
    'reconciliation_warning' => env('ROSTER_RECONCILIATION_WARNING', false),


];
