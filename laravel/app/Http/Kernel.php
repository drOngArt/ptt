<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * @var array
     */
    protected $middleware = [
        'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
        'Illuminate\Cookie\Middleware\EncryptCookies',
        'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
        'Illuminate\Session\Middleware\StartSession',
        'Illuminate\View\Middleware\ShareErrorsFromSession',
        // 'App\Http\Middleware\ExcludeCsrfTokenMiddleware',
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'adminAuth' => \App\Http\Middleware\AdminMiddleware::class,
        'judgeAuth' => \App\Http\Middleware\JudgeMiddleware::class,
        'wallAuth' => \App\Http\Middleware\WallMiddleware::class,
        'auth.basic' => 'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'APIAuth' => \App\Http\Middleware\APIAuth::class,
    ];
}
