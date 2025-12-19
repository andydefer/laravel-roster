<?php

declare(strict_types=1);

use Roster\Models\Availability;
use Roster\Models\Schedule;
use Roster\Models\Impediment;
use Roster\Services\AvailabilityService;
use Roster\Services\ScheduleService;
use Roster\Services\ImpedimentService;
use Roster\Services\Core\ValidationService;
use Roster\Repositories\AvailabilityRepository;
use Roster\Repositories\ScheduleRepository;
use Roster\Repositories\ImpedimentRepository;
use Roster\Exceptions\Messages\ErrorMessageFactory;

/**
 * Roster package configuration file.
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
            'enabled' => env('ROSTER_VALIDATE_AVAILABILITY_FUTURE_DATES', null), // null = use parent
            'allow_past' => env('ROSTER_ALLOW_PAST_AVAILABILITIES', false),
            'field_name' => 'start_date',
        ],
        'schedule' => [
            'enabled' => env('ROSTER_VALIDATE_SCHEDULE_FUTURE_DATES', null), // null = use parent
            'allow_past' => env('ROSTER_ALLOW_PAST_SCHEDULES', false),
            'field_name' => 'start_datetime',
        ],
        'impediment' => [
            'enabled' => env('ROSTER_VALIDATE_IMPEDIMENT_FUTURE_DATES', null), // null = use parent
            'allow_past' => env('ROSTER_ALLOW_PAST_IMPEDIMENTS', false),
            'field_name' => 'start_datetime',
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
        'max_days_to_check' => env('ROSTER_MAX_DAYS_TO_CHECK', 365),
        'max_availability_period_days' => env('ROSTER_MAX_AVAILABILITY_PERIOD_DAYS', 365),
        'max_impediment_duration_days' => env('ROSTER_MAX_IMPEDIMENT_DURATION_DAYS', 30),

        // Scheduling constraints
        'max_schedule_advance_days' => env('ROSTER_MAX_SCHEDULE_ADVANCE_DAYS', 90),
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
        'allowed_statuses' => ['available', 'booked', 'cancelled', 'blocked'],
    ],

    // Impediment management settings
    'impediment' => [
        'require_reason' => env('ROSTER_IMPEDIMENT_REQUIRE_REASON', false),
        'allow_overlap' => env('ROSTER_IMPEDIMENT_ALLOW_OVERLAP', false),
        'max_duration_days' => env('ROSTER_IMPEDIMENT_MAX_DURATION_DAYS', 30),
        'max_future_days' => env('ROSTER_IMPEDIMENT_MAX_FUTURE_DAYS', 365),
    ],

    // Slot finder settings
    'slot_finder' => [
        'search_step_minutes' => env('ROSTER_SLOT_SEARCH_STEP_MINUTES', 15),
        'max_search_days' => env('ROSTER_SLOT_MAX_SEARCH_DAYS', 365),
        'consider_impediments' => env('ROSTER_SLOT_CONSIDER_IMPEDIMENTS', true),
        'consider_schedules' => env('ROSTER_SLOT_CONSIDER_SCHEDULES', true),
        'buffer_minutes' => env('ROSTER_SLOT_BUFFER_MINUTES', 0),

        // Timezone and working hours configuration
        'timezone_aware' => env('ROSTER_SLOT_TIMEZONE_AWARE', true),
        'prefer_working_hours' => env('ROSTER_SLOT_PREFER_WORKING_HOURS', true),
        'working_hours_start' => env('ROSTER_WORKING_HOURS_START', '09:00'),
        'working_hours_end' => env('ROSTER_WORKING_HOURS_END', '17:00'),
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

    // Validation configuration
    'validation' => [
        'strict_mode' => env('ROSTER_VALIDATION_STRICT_MODE', true),
        'throw_exceptions' => env('ROSTER_VALIDATION_THROW_EXCEPTIONS', true),
        'log_validation_errors' => env('ROSTER_VALIDATION_LOG_ERRORS', false),

        // Required fields for each entity
        'required_fields' => [
            'availability' => ['type', 'start_time', 'end_time', 'days'],
            'schedule' => ['title', 'start_datetime', 'end_datetime'],
            'impediment' => ['start_datetime', 'end_datetime'],
        ],

        // Fields that cannot be updated
        'forbidden_update_fields' => ['id', 'created_at', 'updated_at'],

        // Fields that can contain past dates
        'allowed_past_date_fields' => ['end_date', 'end_datetime'],
    ],

    // Logging configuration
    'logging' => [
        'enabled' => env('ROSTER_LOGGING_ENABLED', false),
        'level' => env('ROSTER_LOGGING_LEVEL', 'info'),
        'channel' => env('ROSTER_LOGGING_CHANNEL', 'stack'),

        // Events to log
        'log_events' => [
            'create' => env('ROSTER_LOG_CREATE_EVENTS', true),
            'update' => env('ROSTER_LOG_UPDATE_EVENTS', true),
            'delete' => env('ROSTER_LOG_DELETE_EVENTS', true),
            'overlap' => env('ROSTER_LOG_OVERLAP_EVENTS', true),
            'validation' => env('ROSTER_LOG_VALIDATION_EVENTS', false),
        ],

        // Sensitive fields to mask in logs
        'mask_fields' => ['password', 'token', 'secret', 'api_key'],
    ],

    // Performance optimization settings
    'performance' => [
        'eager_loading' => env('ROSTER_EAGER_LOADING', true),
        'max_query_results' => env('ROSTER_MAX_QUERY_RESULTS', 1000),
        'query_timeout' => env('ROSTER_QUERY_TIMEOUT', 30),
        'enable_query_log' => env('ROSTER_ENABLE_QUERY_LOG', false),

        // Query optimization settings
        'optimize_queries' => env('ROSTER_OPTIMIZE_QUERIES', true),
        'use_subqueries' => env('ROSTER_USE_SUBQUERIES', true),
        'cache_query_results' => env('ROSTER_CACHE_QUERY_RESULTS', true),
    ],

    // Security settings
    'security' => [
        'rate_limit' => [
            'enabled' => env('ROSTER_RATE_LIMIT_ENABLED', false),
            'max_requests_per_minute' => env('ROSTER_RATE_LIMIT_MAX_REQUESTS', 60),
        ],

        'input_sanitization' => env('ROSTER_INPUT_SANITIZATION', true),
        'prevent_sql_injection' => env('ROSTER_PREVENT_SQL_INJECTION', true),
        'validate_input_types' => env('ROSTER_VALIDATE_INPUT_TYPES', true),

        // Update protection to prevent abuse
        'update_protection' => [
            'min_minutes_between_updates' => env('ROSTER_MIN_MINUTES_BETWEEN_UPDATES', 1),
            'max_updates_per_hour' => env('ROSTER_MAX_UPDATES_PER_HOUR', 60),
            'max_updates_per_day' => env('ROSTER_MAX_UPDATES_PER_DAY', 100),
        ],
    ],

    // Testing configuration
    'testing' => [
        'use_fake_dates' => env('ROSTER_TESTING_USE_FAKE_DATES', false),
        'default_test_date' => env('ROSTER_TESTING_DEFAULT_DATE', '2038-06-01'),
        'allow_past_dates_in_tests' => env('ROSTER_TESTING_ALLOW_PAST_DATES', true),
        'disable_cache_in_tests' => env('ROSTER_TESTING_DISABLE_CACHE', true),
        'skip_validation_in_tests' => env('ROSTER_TESTING_SKIP_VALIDATION', false),
    ],

    // Feature flags
    'features' => [
        'enable_recurring_schedules' => env('ROSTER_ENABLE_RECURRING_SCHEDULES', false),
        'enable_bulk_operations' => env('ROSTER_ENABLE_BULK_OPERATIONS', true),
        'enable_ical_export' => env('ROSTER_ENABLE_ICAL_EXPORT', false),
        'enable_api' => env('ROSTER_ENABLE_API', true),
        'enable_web_interface' => env('ROSTER_ENABLE_WEB_INTERFACE', false),
        'enable_notifications' => env('ROSTER_ENABLE_NOTIFICATIONS', false),
        'enable_analytics' => env('ROSTER_ENABLE_ANALYTICS', false),
    ],

    // Customization options
    'custom' => [
        // Custom class bindings
        'models' => [
            'availability' => Availability::class,
            'schedule' => Schedule::class,
            'impediment' => Impediment::class,
        ],

        'services' => [
            'availability' => AvailabilityService::class,
            'schedule' => ScheduleService::class,
            'impediment' => ImpedimentService::class,
            'validation' => ValidationService::class,
        ],

        'repositories' => [
            'availability' => AvailabilityRepository::class,
            'schedule' => ScheduleRepository::class,
            'impediment' => ImpedimentRepository::class,
        ],

        // Custom error messages
        'error_messages' => [
            'overlap' => 'This time slot overlaps with an existing resource.',
            'past_date' => 'Cannot schedule in the past.',
            'minimum_duration' => 'Minimum duration is :minutes minutes.',
            'no_availability' => 'No matching availability found.',
            'invalid_time_range' => 'End time must be after start time.',
        ],

        // Default date/time formats
        'date_format' => env('ROSTER_DATE_FORMAT', 'Y-m-d'),
        'time_format' => env('ROSTER_TIME_FORMAT', 'H:i:s'),
        'datetime_format' => env('ROSTER_DATETIME_FORMAT', 'Y-m-d H:i:s'),
    ],
];
