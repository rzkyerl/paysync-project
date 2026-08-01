<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Activity log timezone
    |--------------------------------------------------------------------------
    |
    | Activity timestamps remain stored in UTC and are converted to this
    | timezone when displayed or filtered.
    |
    */
    'activity_timezone' => env('ACTIVITY_TIMEZONE', 'Asia/Jakarta'),

    /*
    |--------------------------------------------------------------------------
    | Local development team
    |--------------------------------------------------------------------------
    |
    | Accounts created by UserSeeder are linked to the workspace created by
    | this owner account. This keeps the seeded HR, Finance, and Employee
    | accounts together after a fresh migration and onboarding.
    |
    */
    'seeded_team' => [
        'owner' => 'ceo@paysync.test',
        'members' => [
            'hr@paysync.test',
            'finance@paysync.test',
            'employee@paysync.test',
        ],
    ],
];
