<?php

declare(strict_types=1);

/**
 * Roster Package Configuration File.
 *
 * This configuration file provides unified settings for availability, schedule,
 * and impediment management within the Roster package.
 * All values can be overridden via environment variables.
 *
 * @package Roster
 * @category Configuration
 * @license MIT
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Core Settings
    |--------------------------------------------------------------------------
    */

    /**
     * Default timezone for all roster operations.
     * Falls back to application timezone if not set.
     *
     * @var string
     */
    'timezone' => env('ROSTER_TIMEZONE', env('APP_TIMEZONE', 'UTC')),

    /**
     * Enable middleware support for roster routes.
     *
     * @var bool
     */
    'allow_middleware' => true,

    /**
     * Fallback timezone when none is specified.
     *
     * @var string
     */
    'default_timezone' => 'UTC',

    /**
     * List of allowed activity types for availability, schedule, and impediment.
     * When empty, all types are permitted.
     *
     * @var array<int, string>
     * @example ['consultation', 'surgery', 'training']
     */
    'allowed_types' => [],

    /*
    |--------------------------------------------------------------------------
    | Duration Constraints
    |--------------------------------------------------------------------------
    |
    | Controls time intervals and search periods for roster scheduling.
    | These values directly impact system performance and slot generation.
    |
    */

    'durations' => [
        /**
         * Default interval between consecutive time slots in minutes.
         * Affects slot generation granularity.
         *
         * @var int
         * @minimum 10
         */
        'default_slot_interval_minutes' => env('ROSTER_DEFAULT_SLOT_INTERVAL', 10),

        /**
         * Minimum duration for availability in minutes.
         * Values below 10 minutes are automatically enforced to 10 minutes
         * to prevent infinite loops and performance degradation.
         *
         * @var int
         * @minimum 10 (enforced)
         */
        'minimum_availability_minutes' => env('ROSTER_MINIMUM_AVAILABILITY_MINUTES', 10),

        /**
         * Minimum duration for schedule in minutes.
         * Values below 10 minutes are automatically enforced to 10 minutes
         * to prevent infinite loops and performance degradation.
         *
         * @var int
         * @minimum 10 (enforced)
         */
        'minimum_schedule_minutes' => env('ROSTER_MINIMUM_SCHEDULE_MINUTES', 10),

        /**
         * Minimum duration for impediment in minutes.
         * Values below 10 minutes are automatically enforced to 10 minutes
         * to prevent infinite loops and performance degradation.
         *
         * @var int
         * @minimum 10 (enforced)
         */
        'minimum_impediment_minutes' => env('ROSTER_MINIMUM_IMPEDIMENT_MINUTES', 10),

        /**
         * Maximum number of days allowed for date range searches.
         * Limits the search scope to prevent performance issues.
         *
         * @var int
         */
        'max_search_period_days' => env('ROSTER_MAX_SEARCH_PERIOD_DAYS', 365),
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Directories containing custom validation rules for roster operations.
    | The package will scan these directories for rule classes.
    |
    */

    /**
     * Additional directories where custom validation rules are located.
     * Paths should be absolute or relative to the application root.
     *
     * @var array<int, string>
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
        /**
         * Use PostgreSQL exclusion constraints for temporal conflict prevention.
         * Automatically enabled when using PostgreSQL connection.
         *
         * @var bool
         */
        'use_postgres_exclusion_constraints' => env(
            'ROSTER_DB_USE_POSTGRES_EXCLUSION_CONSTRAINTS',
            env('DB_CONNECTION') === 'pgsql'
        ),

        /**
         * Enable database-level check constraints validation.
         * Automatically enabled when using MySQL connection.
         *
         * @var bool
         */
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
        /**
         * Enable file-based caching for validation rules.
         *
         * @var bool
         */
        'use_file_cache' => env('ROSTER_USE_FILE_CACHE', true),

        /**
         * Path where the cached validation rules are stored.
         *
         * @var string
         */
        'cache_file' => storage_path('framework/cache/roster_rules.php'),

        /**
         * Maximum age of cached rules before regeneration in hours.
         *
         * @var int
         */
        'cache_max_age_hours' => env('ROSTER_CACHE_MAX_AGE', 24),

        /**
         * Always enable caching in production environment.
         * Automatically sets cache.enabled = true when APP_ENV=production.
         *
         * @var bool
         */
        'always_cache_in_production' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Reconciliation Settings
    |--------------------------------------------------------------------------
    |
    | Determines how the system handles days outside validity periods during updates.
    | When enabled, warnings will be triggered; when disabled, days are silently
    | reconciled.
    |
    */

    /**
     * Enable warning when days are automatically filtered because they fall
     * outside the validity period.
     *
     * @var bool
     * @example true  - Triggers E_USER_WARNING for debugging
     * @example false - Silently reconciles days without warnings
     */
    'reconciliation_warning' => env('ROSTER_RECONCILIATION_WARNING', false),
];
