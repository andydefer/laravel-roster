<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Rules Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the validation rules system.
    | You can customize which rules are applied to which entities and operations.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Durations
    |--------------------------------------------------------------------------
    |
    | Minimum durations in minutes for different entity types.
    | These values are used by the DurationRule.
    |
    */
    'minimum_durations' => [
        'availability' => 15,
        'schedule' => 15,
        'impediment' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Date Validation Settings
    |--------------------------------------------------------------------------
    */
    'max_days' => 365,
    'validate_future_dates' => true,
    'allow_past_dates' => false,

    /*
    |--------------------------------------------------------------------------
    | Default Timezone
    |--------------------------------------------------------------------------
    |
    | Default timezone used when none is provided in the data.
    |
    */
    'default_timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Custom Rule Directories
    |--------------------------------------------------------------------------
    |
    | You can add custom validation rules by adding their paths here.
    | Rules will be auto-discovered from these directories.
    |
    */
    'rule_directories' => [
        // app_path('Validation/Rules'),
    ],

    'availability_types' => [],
    'with_cache' => false,

];
