<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',

        using: function () {

            // WEB
            // Zachowujemy stary namespace kontrolerów.
            Route::middleware('web')
                ->namespace('App\Http\Controllers')
                ->group(base_path('routes/web.php'));

            // API
            // Celowo bez prefix('api'), ponieważ routes/api.php
            // zawiera już api/v1.
            Route::middleware('api')
                ->namespace('App\Http\Controllers')
                ->group(base_path('routes/api.php'));
        },
    )

    ->withMiddleware(function (Middleware $middleware): void {

       $middleware->alias([
          'adminAuth' => \App\Http\Middleware\AdminMiddleware::class,
          'judgeAuth' => \App\Http\Middleware\JudgeMiddleware::class,
          'wallAuth'  => \App\Http\Middleware\WallMiddleware::class,
          'APIAuth'   => \App\Http\Middleware\APIAuth::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->stopIgnoring(TokenMismatchException::class);
        // Tymczasowa diagnostyka losowego błędu 419
        $exceptions->report(function (TokenMismatchException $e) {

            $request = request();

            Log::error('CSRF 419 TOKEN MISMATCH', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),

                'user_id' => auth()->id(),

                'session_id' => $request->hasSession()
                    ? $request->session()->getId()
                    : null,

                'session_token' => $request->hasSession()
                    ? $request->session()->token()
                    : null,

                'request_token' => $request->input('_token'),

                'referer' => $request->header('referer'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'time' => now()->toDateTimeString(),
            ]);
        });
    })

    ->create();