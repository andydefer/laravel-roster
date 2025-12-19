<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Core Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration de base du package Roster.
    |
    */

    'timezone' => env('ROSTER_TIMEZONE', env('APP_TIMEZONE', 'UTC')),

    'validate_future_dates' => env('ROSTER_VALIDATE_FUTURE_DATES', true),

    /*
    |--------------------------------------------------------------------------
    | Durations Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration des durées pour les différentes entités.
    |
    */

    'durations' => [
        // Durées minimales
        'minimum_impediment_minutes' => env('ROSTER_MIN_IMPEDIMENT_MINUTES', 5),
        'minimum_schedule_minutes' => env('ROSTER_MIN_SCHEDULE_MINUTES', 15),
        'minimum_availability_minutes' => env('ROSTER_MIN_AVAILABILITY_MINUTES', 15),

        // Durées par défaut
        'default_slot_duration_minutes' => env('ROSTER_DEFAULT_SLOT_DURATION', 60),
        'default_slot_interval_minutes' => env('ROSTER_DEFAULT_SLOT_INTERVAL', 30),

        // Limites temporelles
        'max_days_to_check' => env('ROSTER_MAX_DAYS_TO_CHECK', 365),
        'max_availability_period_days' => env('ROSTER_MAX_AVAILABILITY_PERIOD_DAYS', 365),
        'max_impediment_duration_days' => env('ROSTER_MAX_IMPEDIMENT_DURATION_DAYS', 30),

        // Avance maximale pour la planification
        'max_schedule_advance_days' => env('ROSTER_MAX_SCHEDULE_ADVANCE_DAYS', 90),
        'min_schedule_advance_minutes' => env('ROSTER_MIN_SCHEDULE_ADVANCE_MINUTES', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Availability Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration spécifique aux disponibilités.
    |
    */

    'availability' => [
        'merge_adjacent' => env('ROSTER_AVAILABILITY_MERGE_ADJACENT', true),
        'allow_overlap' => env('ROSTER_AVAILABILITY_ALLOW_OVERLAP', false),
        'validate_date_ranges' => env('ROSTER_AVAILABILITY_VALIDATE_DATE_RANGES', true),
        'minimum_days' => env('ROSTER_AVAILABILITY_MINIMUM_DAYS', 1),
        'maximum_concurrent_availabilities' => env('ROSTER_AVAILABILITY_MAX_CONCURRENT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Schedule Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration spécifique aux plannings.
    |
    */

    'schedule' => [
        'default_status' => env('ROSTER_SCHEDULE_DEFAULT_STATUS', 'available'),
        'allow_past_schedules' => env('ROSTER_SCHEDULE_ALLOW_PAST', false),
        'allow_overlap' => env('ROSTER_SCHEDULE_ALLOW_OVERLAP', false),
        'cancellation_lead_time_minutes' => env('ROSTER_SCHEDULE_CANCELLATION_LEAD_TIME', 60),
        'rescheduling_lead_time_minutes' => env('ROSTER_SCHEDULE_RESCHEDULING_LEAD_TIME', 30),

        // Status autorisés
        'allowed_statuses' => [
            'available',
            'booked',
            'cancelled',
            'blocked',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Impediment Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration spécifique aux impediments (blocages).
    |
    */

    'impediment' => [
        'require_reason' => env('ROSTER_IMPEDIMENT_REQUIRE_REASON', false),
        'allow_overlap' => env('ROSTER_IMPEDIMENT_ALLOW_OVERLAP', false),
        'allow_past_impediments' => env('ROSTER_IMPEDIMENT_ALLOW_PAST', false),
        'max_duration_days' => env('ROSTER_IMPEDIMENT_MAX_DURATION_DAYS', 30),
        'max_future_days' => env('ROSTER_IMPEDIMENT_MAX_FUTURE_DAYS', 365),
    ],

    /*
    |--------------------------------------------------------------------------
    | Slot Finder Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour la recherche de créneaux disponibles.
    |
    */

    'slot_finder' => [
        'search_step_minutes' => env('ROSTER_SLOT_SEARCH_STEP_MINUTES', 15),
        'max_search_days' => env('ROSTER_SLOT_MAX_SEARCH_DAYS', 365),
        'consider_impediments' => env('ROSTER_SLOT_CONSIDER_IMPEDIMENTS', true),
        'consider_schedules' => env('ROSTER_SLOT_CONSIDER_SCHEDULES', true),
        'buffer_minutes' => env('ROSTER_SLOT_BUFFER_MINUTES', 0),

        // Fuseaux horaires pour la recherche
        'timezone_aware' => env('ROSTER_SLOT_TIMEZONE_AWARE', true),
        'prefer_working_hours' => env('ROSTER_SLOT_PREFER_WORKING_HOURS', true),
        'working_hours_start' => env('ROSTER_WORKING_HOURS_START', '09:00'),
        'working_hours_end' => env('ROSTER_WORKING_HOURS_END', '17:00'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration spécifique à la base de données.
    |
    */

    'database' => [
        'use_json_constraints' => env('ROSTER_DB_USE_JSON_CONSTRAINTS', env('DB_CONNECTION') === 'pgsql'),
        'check_constraints' => env('ROSTER_DB_CHECK_CONSTRAINTS', env('DB_CONNECTION') === 'mysql'),
        'enable_foreign_keys' => env('ROSTER_DB_ENABLE_FOREIGN_KEYS', true),
        'enable_indexes' => env('ROSTER_DB_ENABLE_INDEXES', true),
        'enable_unique_constraints' => env('ROSTER_DB_ENABLE_UNIQUE_CONSTRAINTS', true),

        // Configuration des migrations
        'migrations' => [
            'publish' => env('ROSTER_PUBLISH_MIGRATIONS', true),
            'auto_run' => env('ROSTER_AUTO_RUN_MIGRATIONS', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration du cache pour l'optimisation des performances.
    |
    */

    'cache' => [
        'enabled' => env('ROSTER_CACHE_ENABLED', true),
        'driver' => env('ROSTER_CACHE_DRIVER', env('CACHE_DRIVER', 'file')),
        'ttl' => env('ROSTER_CACHE_TTL', 3600), // secondes
        'prefix' => env('ROSTER_CACHE_PREFIX', 'roster_'),

        // Configuration spécifique par entité
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

        // Tags de cache
        'use_tags' => env('ROSTER_CACHE_USE_TAGS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour les validations.
    |
    */

    'validation' => [
        'strict_mode' => env('ROSTER_VALIDATION_STRICT_MODE', true),
        'throw_exceptions' => env('ROSTER_VALIDATION_THROW_EXCEPTIONS', true),
        'log_validation_errors' => env('ROSTER_VALIDATION_LOG_ERRORS', false),

        // Champs obligatoires
        'required_fields' => [
            'availability' => ['type', 'start_time', 'end_time', 'days'],
            'schedule' => ['title', 'start_datetime', 'end_datetime'],
            'impediment' => ['start_datetime', 'end_datetime'],
        ],

        // Champs interdits pour les mises à jour
        'forbidden_update_fields' => ['id', 'created_at', 'updated_at'],

        // Champs autorisés à avoir des dates passées
        'allowed_past_date_fields' => ['end_date', 'end_datetime'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour la journalisation.
    |
    */

    'logging' => [
        'enabled' => env('ROSTER_LOGGING_ENABLED', false),
        'level' => env('ROSTER_LOGGING_LEVEL', 'info'),
        'channel' => env('ROSTER_LOGGING_CHANNEL', 'stack'),

        // Événements à logger
        'log_events' => [
            'create' => env('ROSTER_LOG_CREATE_EVENTS', true),
            'update' => env('ROSTER_LOG_UPDATE_EVENTS', true),
            'delete' => env('ROSTER_LOG_DELETE_EVENTS', true),
            'overlap' => env('ROSTER_LOG_OVERLAP_EVENTS', true),
            'validation' => env('ROSTER_LOG_VALIDATION_EVENTS', false),
        ],

        // Informations sensibles à masquer
        'mask_fields' => [
            'password',
            'token',
            'secret',
            'api_key',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour l'optimisation des performances.
    |
    */

    'performance' => [
        'eager_loading' => env('ROSTER_EAGER_LOADING', true),
        'max_query_results' => env('ROSTER_MAX_QUERY_RESULTS', 1000),
        'query_timeout' => env('ROSTER_QUERY_TIMEOUT', 30), // secondes
        'enable_query_log' => env('ROSTER_ENABLE_QUERY_LOG', false),

        // Optimisation des requêtes
        'optimize_queries' => env('ROSTER_OPTIMIZE_QUERIES', true),
        'use_subqueries' => env('ROSTER_USE_SUBQUERIES', true),
        'cache_query_results' => env('ROSTER_CACHE_QUERY_RESULTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration de sécurité.
    |
    */

    'security' => [
        'rate_limit' => [
            'enabled' => env('ROSTER_RATE_LIMIT_ENABLED', false),
            'max_requests_per_minute' => env('ROSTER_RATE_LIMIT_MAX_REQUESTS', 60),
        ],

        'input_sanitization' => env('ROSTER_INPUT_SANITIZATION', true),
        'prevent_sql_injection' => env('ROSTER_PREVENT_SQL_INJECTION', true),
        'validate_input_types' => env('ROSTER_VALIDATE_INPUT_TYPES', true),

        // Protection contre les mises à jour fréquentes
        'update_protection' => [
            'min_minutes_between_updates' => env('ROSTER_MIN_MINUTES_BETWEEN_UPDATES', 1),
            'max_updates_per_hour' => env('ROSTER_MAX_UPDATES_PER_HOUR', 60),
            'max_updates_per_day' => env('ROSTER_MAX_UPDATES_PER_DAY', 100),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Testing Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour les tests.
    |
    */

    'testing' => [
        'use_fake_dates' => env('ROSTER_TESTING_USE_FAKE_DATES', false),
        'default_test_date' => env('ROSTER_TESTING_DEFAULT_DATE', '2038-06-01'),
        'allow_past_dates_in_tests' => env('ROSTER_TESTING_ALLOW_PAST_DATES', true),
        'disable_cache_in_tests' => env('ROSTER_TESTING_DISABLE_CACHE', true),
        'skip_validation_in_tests' => env('ROSTER_TESTING_SKIP_VALIDATION', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Activation/désactivation des fonctionnalités.
    |
    */

    'features' => [
        'enable_recurring_schedules' => env('ROSTER_ENABLE_RECURRING_SCHEDULES', false),
        'enable_bulk_operations' => env('ROSTER_ENABLE_BULK_OPERATIONS', true),
        'enable_ical_export' => env('ROSTER_ENABLE_ICAL_EXPORT', false),
        'enable_api' => env('ROSTER_ENABLE_API', true),
        'enable_web_interface' => env('ROSTER_ENABLE_WEB_INTERFACE', false),
        'enable_notifications' => env('ROSTER_ENABLE_NOTIFICATIONS', false),
        'enable_analytics' => env('ROSTER_ENABLE_ANALYTICS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Customization
    |--------------------------------------------------------------------------
    |
    | Personnalisation du comportement.
    |
    */

    'custom' => [
        // Classes personnalisées
        'models' => [
            'availability' => \Roster\Models\Availability::class,
            'schedule' => \Roster\Models\Schedule::class,
            'impediment' => \Roster\Models\Impediment::class,
        ],

        'services' => [
            'availability' => \Roster\Services\AvailabilityService::class,
            'schedule' => \Roster\Services\ScheduleService::class,
            'impediment' => \Roster\Services\ImpedimentService::class,
            'validation' => \Roster\Services\Core\ValidationService::class,
        ],

        'repositories' => [
            'availability' => \Roster\Repositories\AvailabilityRepository::class,
            'schedule' => \Roster\Repositories\ScheduleRepository::class,
            'impediment' => \Roster\Repositories\ImpedimentRepository::class,
        ],

        // Personnalisation des messages d'erreur
        'error_messages' => [
            'overlap' => 'This time slot overlaps with an existing resource.',
            'past_date' => 'Cannot schedule in the past.',
            'minimum_duration' => 'Minimum duration is :minutes minutes.',
            'no_availability' => 'No matching availability found.',
            'invalid_time_range' => 'End time must be after start time.',
        ],

        // Formatage par défaut
        'date_format' => env('ROSTER_DATE_FORMAT', 'Y-m-d'),
        'time_format' => env('ROSTER_TIME_FORMAT', 'H:i:s'),
        'datetime_format' => env('ROSTER_DATETIME_FORMAT', 'Y-m-d H:i:s'),
    ],
];
