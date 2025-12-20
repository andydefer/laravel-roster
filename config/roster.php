<?php

declare(strict_types=1);

/**
 * Configuration settings for the Roster package.
 *
 * This configuration file defines all customizable settings for the Roster package.
 * Environment variables can be used to override any default values.
 *
 * @package Roster\Config
 */
return [
    // Core settings
    'timezone' => env('ROSTER_TIMEZONE', env('APP_TIMEZONE', 'UTC')),

    // Future dates validation - unified setting
    'validate_future_dates' => [
        'enabled' => env('ROSTER_VALIDATE_FUTURE_DATES', true),

        // Entity-specific overrides
        'availability' => [
            'enabled' => env('ROSTER_VALIDATE_AVAILABILITY_FUTURE_DATES'), // null = use parent
            'allow_past' => env('ROSTER_ALLOW_PAST_AVAILABILITIES', false),
            'validation_field' => 'start_date',
        ],
        'schedule' => [
            'enabled' => env('ROSTER_VALIDATE_SCHEDULE_FUTURE_DATES'), // null = use parent
            'allow_past' => env('ROSTER_ALLOW_PAST_SCHEDULES', false),
            'validation_field' => 'start_datetime',
        ],
        'impediment' => [
            'enabled' => env('ROSTER_VALIDATE_IMPEDIMENT_FUTURE_DATES'), // null = use parent
            'allow_past' => env('ROSTER_ALLOW_PAST_IMPEDIMENTS', false),
            'validation_field' => 'start_datetime',
        ],
    ],

    // Duration constraints
    'durations' => [
        // Minimum durations in minutes
        'minimum_impediment_minutes' => env('ROSTER_MIN_IMPEDIMENT_MINUTES', 5),
        'minimum_schedule_minutes' => env('ROSTER_MIN_SCHEDULE_MINUTES', 15),
        'minimum_availability_minutes' => env('ROSTER_MIN_AVAILABILITY_MINUTES', 15),

        // Default slot settings
        'default_slot_duration_minutes' => env('ROSTER_DEFAULT_SLOT_DURATION', 60),
        'default_slot_interval_minutes' => env('ROSTER_DEFAULT_SLOT_INTERVAL', 30),

        // Time boundaries
        'max_search_period_days' => env('ROSTER_max_search_period_days', 365),
        'max_availability_period_days' => env('ROSTER_MAX_AVAILABILITY_PERIOD_DAYS', 365),
        'max_impediment_duration_days' => env('ROSTER_MAX_IMPEDIMENT_DURATION_DAYS', 30),

        // Scheduling constraints
        'max_scheduling_horizon_days' => env('ROSTER_max_scheduling_horizon_days', 90),
        'min_schedule_advance_minutes' => env('ROSTER_MIN_SCHEDULE_ADVANCE_MINUTES', 30),
    ],

    // Availability management settings
    'availability' => [
        'merge_adjacent' => env('ROSTER_AVAILABILITY_MERGE_ADJACENT', true),
        'allow_overlap' => env('ROSTER_AVAILABILITY_ALLOW_OVERLAP', false),
        'validate_date_ranges' => env('ROSTER_AVAILABILITY_VALIDATE_DATE_RANGES', true),
        'minimum_days' => env('ROSTER_AVAILABILITY_MINIMUM_DAYS', 1),
        'maximum_concurrent_availabilities' => env('ROSTER_AVAILABILITY_MAX_CONCURRENT', 10),
    ],

    // Schedule management settings
    'schedule' => [
        'default_status' => env('ROSTER_SCHEDULE_DEFAULT_STATUS', 'available'),
        'allow_overlap' => env('ROSTER_SCHEDULE_ALLOW_OVERLAP', false),
        'cancellation_lead_time_minutes' => env('ROSTER_SCHEDULE_CANCELLATION_LEAD_TIME', 60),
        'rescheduling_lead_time_minutes' => env('ROSTER_SCHEDULE_RESCHEDULING_LEAD_TIME', 30),

        // Allowed schedule statuses
        'valid_statuses' => ['available', 'booked', 'cancelled', 'blocked'],
    ],

    // Impediment management settings
    'impediment' => [
        'require_reason' => env('ROSTER_IMPEDIMENT_REQUIRE_REASON', false),
        'allow_overlap' => env('ROSTER_IMPEDIMENT_ALLOW_OVERLAP', false),
        'max_duration_days' => env('ROSTER_IMPEDIMENT_MAX_DURATION_DAYS', 30),
        'max_future_days' => env('ROSTER_IMPEDIMENT_MAX_FUTURE_DAYS', 365),
    ],

    // Database configuration
    'database' => [
        'use_json_constraints' => env('ROSTER_DB_USE_JSON_CONSTRAINTS', env('DB_CONNECTION') === 'pgsql'),
        'check_constraints' => env('ROSTER_DB_CHECK_CONSTRAINTS', env('DB_CONNECTION') === 'mysql'),
        'enable_foreign_keys' => env('ROSTER_DB_ENABLE_FOREIGN_KEYS', true),
        'enable_indexes' => env('ROSTER_DB_ENABLE_INDEXES', true),
        'enable_unique_constraints' => env('ROSTER_DB_ENABLE_UNIQUE_CONSTRAINTS', true),

        // Migration settings
        'migrations' => [
            'publish' => env('ROSTER_PUBLISH_MIGRATIONS', true),
            'auto_run' => env('ROSTER_AUTO_RUN_MIGRATIONS', false),
        ],
    ],

    // Cache configuration
    'cache' => [
        'enabled' => env('ROSTER_CACHE_ENABLED', true),
        'driver' => env('ROSTER_CACHE_DRIVER', env('CACHE_DRIVER', 'file')),
        'ttl' => env('ROSTER_CACHE_TTL', 3600),
        'prefix' => env('ROSTER_CACHE_PREFIX', 'roster_'),

        // Entity-specific cache settings
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
