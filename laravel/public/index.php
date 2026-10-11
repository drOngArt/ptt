<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

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

/*
|--------------------------------------------------------------------------
| Handle The Request
|--------------------------------------------------------------------------
*/

$app->handleRequest(Request::capture());