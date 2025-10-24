<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
    \App\Events\SomethingHappened::class => [
        \App\Listeners\EventListener::class,
    ],
];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
