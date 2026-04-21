<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
      /*if (app()->runningInConsole()) {
        return;
      } 

      try {
          Log::info('SESSION TRACE', [
              'time' => now()->toDateTimeString(),
              'session_id' => session()->getId(),
              'user' => Auth::id(),
              'auth' => Auth::check(),
              'ip' => request()->ip(),
              'cookie' => request()->cookie('laravel_session'),
              'url' => request()->fullUrl(),
          ]);
      } catch (\Throwable $e) {
          Log::error('SESSION DEBUG ERROR: '.$e->getMessage());
      }*/
    }

    /**
     * Register any application services.
     *
     * This service provider is a great spot to register your various container
     * bindings with the application. As you can see, we are registering our
     * "Registrar" implementation here. You can add your own bindings too!
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            'Illuminate\Contracts\Auth\Registrar',
            \App\Services\Registrar::class
        );
    }
}
