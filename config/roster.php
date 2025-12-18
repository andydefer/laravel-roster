<?php

// ==== config/roster.php ====

return [
    /*
    |--------------------------------------------------------------------------
    | No Default Schedulable Model
    |--------------------------------------------------------------------------
    |
    | This package is designed to work with any model that uses
    | the Roster\Traits\HasRoster trait. No default model is specified.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Slot Duration
    |--------------------------------------------------------------------------
    |
    | The default length of each time slot, in minutes.
    |
    */
    'default_slot_duration' => 60,

    /*
    |--------------------------------------------------------------------------
    | Default Slot Interval
    |--------------------------------------------------------------------------
    |
    | The default interval between consecutive slots, in minutes.
    |
    */
    'default_slot_interval' => 30,

    /*
    |--------------------------------------------------------------------------
    | Timezone
    |--------------------------------------------------------------------------
    |
    | The default timezone used for scheduling.
    |
    */
    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Days of the Week
    |--------------------------------------------------------------------------
    |
    | Defines the standard days of the week for recurring availabilities.
    |
    */
    'days_of_week' => [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ],

    /*
    |--------------------------------------------------------------------------
    | Activity Types
    |--------------------------------------------------------------------------
    |
    | Defines the possible types of activities that can be scheduled.
    |
    */
    'activity_types' => [
        'consultation',
        'training',
        'meeting',
        'coaching',
        'appointment',
        'other',
    ],

    /*
    |--------------------------------------------------------------------------
    | Schedule Statuses
    |--------------------------------------------------------------------------
    |
    | Defines the possible statuses for schedules.
    |
    */
    'schedule_statuses' => [
        'available',
        'booked',
        'cancelled',
        'blocked',
    ],
];
