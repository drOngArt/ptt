<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\TournamentServiceProvider::class,

    Spatie\Html\HtmlServiceProvider::class,

    Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class,
    Barryvdh\Debugbar\ServiceProvider::class,
];