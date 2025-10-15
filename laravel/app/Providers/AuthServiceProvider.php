<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Contracts\Auth\Access\Gate as GateContract;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * @return void
     */
    public function boot( GateContract $gate )
    {
        $this->registerPolicies( $gate );
        
        $gate->define('admin-only', function ($user) {
            return $user->role === 'admin';  // albo inny warunek
        });

        $gate->define('judge-only', function ($user) {
            return $user->role === 'judge';  // albo inny warunek
        });

        $gate->define('wall-only', function ($user) {
            return $user->role === 'wall';  // albo inny warunek
        });
    }
}