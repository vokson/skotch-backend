<?php

use Illuminate\Support\Str;

return [

    'default' => env('DB_CONNECTION'),
    'backup_lifetime' =>env('DB_BACKUP_LIFETIME',  14*24*60*60), // 2 weeks

    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],
    ],

    'migrations' => 'migrations',
];
