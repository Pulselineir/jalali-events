<?php

return [
    'storage' => env(
        'JALALI_EVENTS_STORAGE',
        'database'
    ),

    'source' => 'time.ir',

    'update' => [
        'enabled' => env(
            'JALALI_EVENTS_AUTO_UPDATE',
            true
        ),

        'frequency' => env(
            'JALALI_EVENTS_UPDATE_FREQUENCY',
            'monthly'
        ),
    ],

    'json' => [
        'path' => storage_path(
            'app/jalali-events/calendar.json'
        ),
    ],

    'database' => [
        'connection' => env(
            'JALALI_EVENTS_DB_CONNECTION'
        ),

        'days_table' => 'jalali_calendar_days',

        'events_table' => 'jalali_events',
    ],

    'http' => [
        'timeout' => env(
            'JALALI_EVENTS_HTTP_TIMEOUT',
            15
        ),

        'retry' => env(
            'JALALI_EVENTS_HTTP_RETRY',
            3
        ),

        'retry_sleep' => env(
            'JALALI_EVENTS_HTTP_RETRY_SLEEP',
            1000
        ),

        'verify_ssl' => env(
            'JALALI_EVENTS_VERIFY_SSL',
            true
        ),

        'user_agent' => env(
            'JALALI_EVENTS_USER_AGENT',
            'JalaliEvents Laravel Package'
        ),
    ],
];
