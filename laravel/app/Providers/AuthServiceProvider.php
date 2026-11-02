<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('admin-only', function ($user) {
            return $user->role === 'admin';
        });

        Gate::define('judge-only', function ($user) {
            return $user->role === 'judge';
        });

        Gate::define('wall-only', function ($user) {
            return $user->role === 'wall';
        });
    }
}