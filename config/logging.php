<?php

use Monolog\Handler\NullHandler;

return [

    'default' => env('LOG_CHANNEL', 'stack'),

    'deprecations' => [
        'channel' => 'null',
        'trace' => false,
    ],

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['error_file'],
            'ignore_exceptions' => false,
        ],

        'error_file' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel-errors.log'),
            'level' => 'error',
            'locking' => false,
        ],

        'info_file' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel-info.log'),
            'level' => 'info',
            'locking' => false,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],
    ],
];
