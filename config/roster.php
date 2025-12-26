<?php

declare(strict_types=1);

/**
 * Unified configuration for the Roster package.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Core & Timezone
    |--------------------------------------------------------------------------
    */
    'timezone' => env('ROSTER_TIMEZONE', env('APP_TIMEZONE', 'UTC')),
    'default_timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Future & Past Date Validation
    |--------------------------------------------------------------------------
    */
    'validate_future_dates' => [
        'enabled' => env('ROSTER_VALIDATE_FUTURE_DATES', true),
        'allow_past' => env('ROSTER_ALLOW_PAST_DATES', false),
        'validation_field' => 'start_datetime',
    ],

    // Backward compatibility
    'allow_past_dates' => env('ROSTER_ALLOW_PAST_DATES', false),

    /*
    |--------------------------------------------------------------------------
    | Duration Constraints
    |--------------------------------------------------------------------------
    */
    'minimum_durations' => [
        'availability' => env('ROSTER_MIN_AVAILABILITY_MINUTES', 15),
        'schedule' => env('ROSTER_MIN_SCHEDULE_MINUTES', 15),
        'impediment' => env('ROSTER_MIN_IMPEDIMENT_MINUTES', 5),
    ],

    'durations' => [
        'default_slot_duration_minutes' => env('ROSTER_DEFAULT_SLOT_DURATION', 60),
        'default_slot_interval_minutes' => env('ROSTER_DEFAULT_SLOT_INTERVAL', 30),

        'max_search_period_days' => env('ROSTER_MAX_SEARCH_PERIOD_DAYS', 365),
        'max_availability_period_days' => env('ROSTER_MAX_AVAILABILITY_PERIOD_DAYS', 365),
        'max_impediment_duration_days' => env('ROSTER_MAX_IMPEDIMENT_DURATION_DAYS', 30),

        'max_scheduling_horizon_days' => env('ROSTER_MAX_SCHEDULING_HORIZON_DAYS', 90),
        'min_schedule_advance_minutes' => env('ROSTER_MIN_SCHEDULE_ADVANCE_MINUTES', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Availability Settings
    |--------------------------------------------------------------------------
    */
    'availability' => [
        'types' => [],
        'merge_adjacent' => env('ROSTER_AVAILABILITY_MERGE_ADJACENT', true),
        'allow_overlap' => env('ROSTER_AVAILABILITY_ALLOW_OVERLAP', false),
        'validate_date_ranges' => env('ROSTER_AVAILABILITY_VALIDATE_DATE_RANGES', true),
        'minimum_days' => env('ROSTER_AVAILABILITY_MINIMUM_DAYS', 1),
        'maximum_concurrent' => env('ROSTER_AVAILABILITY_MAX_CONCURRENT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Schedule Settings
    |--------------------------------------------------------------------------
    */
    'schedule' => [
        'default_status' => env('ROSTER_SCHEDULE_DEFAULT_STATUS', 'available'),
        'allow_overlap' => env('ROSTER_SCHEDULE_ALLOW_OVERLAP', false),
        'cancellation_lead_time_minutes' => env('ROSTER_SCHEDULE_CANCELLATION_LEAD_TIME', 60),
        'rescheduling_lead_time_minutes' => env('ROSTER_SCHEDULE_RESCHEDULING_LEAD_TIME', 30),
        'valid_statuses' => ['available', 'booked', 'cancelled', 'blocked'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Impediment Settings
    |--------------------------------------------------------------------------
    */
    'impediment' => [
        'require_reason' => env('ROSTER_IMPEDIMENT_REQUIRE_REASON', false),
        'allow_overlap' => env('ROSTER_IMPEDIMENT_ALLOW_OVERLAP', false),
        'max_duration_days' => env('ROSTER_IMPEDIMENT_MAX_DURATION_DAYS', 30),
        'max_future_days' => env('ROSTER_IMPEDIMENT_MAX_FUTURE_DAYS', 365),
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    */
    'rule_directories' => [
        // app_path('Validation/Rules'),
    ],

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
        'enable_foreign_keys' => env('ROSTER_DB_ENABLE_FOREIGN_KEYS', true),
        'enable_indexes' => env('ROSTER_DB_ENABLE_INDEXES', true),
        'enable_unique_constraints' => env('ROSTER_DB_ENABLE_UNIQUE_CONSTRAINTS', true),

        'migrations' => [
            'publish' => env('ROSTER_PUBLISH_MIGRATIONS', true),
            'auto_run' => env('ROSTER_AUTO_RUN_MIGRATIONS', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    */
    'with_cache' => true,

    'cache' => [
        'enabled' => env('ROSTER_CACHE_ENABLED', true),
        'driver' => env('ROSTER_CACHE_DRIVER', env('CACHE_DRIVER', 'file')),
        'ttl' => env('ROSTER_CACHE_TTL', 3600),
        'prefix' => env('ROSTER_CACHE_PREFIX', 'roster_'),

        // File-based rule cache (validation rules)
        'use_file_cache' => env('ROSTER_USE_FILE_CACHE', true),
        'cache_file' => storage_path('framework/cache/roster_rules.php'),
        'cache_max_age_hours' => env('ROSTER_CACHE_MAX_AGE', 24),
        'always_cache_in_production' => true,

        // Entity caches
        'availability' => [
            'enabled' => env('ROSTER_CACHE_AVAILABILITY_ENABLED', true),
            'ttl' => env('ROSTER_CACHE_AVAILABILITY_TTL', 1800),
        ],
        'schedule' => [
            'enabled' => env('ROSTER_CACHE_SCHEDULE_ENABLED', true),
            'ttl' => env('ROSTER_CACHE_SCHEDULE_TTL', 900),
        ],
        'impediment' => [
            'enabled' => env('ROSTER_CACHE_IMPEDIMENT_ENABLED', true),
            'ttl' => env('ROSTER_CACHE_IMPEDIMENT_TTL', 1800),
        ],

        'use_tags' => env('ROSTER_CACHE_USE_TAGS', true),
    ],
];
