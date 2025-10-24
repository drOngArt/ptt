<?php

use Monolog\Handler\StreamHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Domyślny kanał logowania
    |--------------------------------------------------------------------------
    |
    | Tutaj określasz który kanał logowania będzie używany, gdy wywołasz
    | Log::info(...), Log::error(...) itp.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Kanały logowania
    |--------------------------------------------------------------------------
    |
    | Tutaj definiujesz poszczególne „kanały” logowania — pliki, konsola,
    | syslog, errorlog itp.
    |
    */

    'channels' => [

        'stack' => [
            'driver'            => 'stack',
            'channels'          => ['single', 'stdout', 'stderr'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path'   => storage_path('logs/laravel.log'),
            'level'  => env('LOG_LEVEL', 'debug'),
        ],

        'daily' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/laravel.log'),
            'level'  => env('LOG_LEVEL', 'debug'),
            'days'   => 14,
        ],
        
        'stderr' => [
            'driver'  => 'monolog',
            'handler' => StreamHandler::class,
            'with'    => [
               'stream' => 'php://stderr',
            ],
            'level'   => env('LOG_LEVEL', 'debug'),
         ],


        'stdout' => [
            'driver'  => 'monolog',
            'handler' => StreamHandler::class,
            'with'    => [
                'stream' => 'php://stdout',
            ],
            'level'   => env('LOG_LEVEL', 'debug'),
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level'  => env('LOG_LEVEL', 'debug'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level'  => env('LOG_LEVEL', 'debug'),
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];
