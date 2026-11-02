<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
*/

require __DIR__.'/laravel/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Bootstrap Laravel
|--------------------------------------------------------------------------
*/

$app = require_once __DIR__.'/laravel/bootstrap/app.php';

// Automatically create configure cache (in folder /bootstrap/cache)
$cacheFile = $app->getCachedConfigPath();

if (!is_file($cacheFile)) {
    $kernel = $app->make(
        \Illuminate\Contracts\Console\Kernel::class
    );

    if ($kernel->call('config:cache') !== 0) {
        throw new \RuntimeException(
            'Nie udało się utworzyć cache konfiguracji.'
        );
    }
}

/*
|--------------------------------------------------------------------------
| Handle The Request
|--------------------------------------------------------------------------
*/

$app->handleRequest(Request::capture());