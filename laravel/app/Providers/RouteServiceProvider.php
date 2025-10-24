<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'App\Http\Controllers';

    public function boot()
    {
        parent::boot();
    }

    public function map()
    {
        $this->mapApiRoutes();
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes()
    {
        Route::group([
            'middleware' => 'web',
            'namespace'  => $this->namespace,
        ], function () {
            require base_path('routes/web.php');
        });
    }

    protected function mapApiRoutes()
    {
        // Bez prefix('api') – bo masz już 'api/v1' w routes/api.php
        Route::group([
            'middleware' => 'api',
            'namespace'  => $this->namespace,
        ], function () {
            require base_path('routes/api.php');
        });
    }
}
