<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\APICompetitionController;
use App\Http\Controllers\API\APIJudgeLoginController;
use App\Http\Controllers\API\APIJudgeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('api/v1')->group(function () {

    /*
     | Publiczne API
     */
    Route::get('/competition', [APICompetitionController::class, 'getCompetition'])
        ->name('api.competition');

    Route::get('/adjudicators', [APICompetitionController::class, 'getAdjudicators'])
        ->name('api.adjudicators');

    Route::post('/login', [APIJudgeLoginController::class, 'postLogin'])
        ->name('api.login');

    /*
     | API wymagające autoryzacji
     */
    Route::middleware('APIAuth')->group(function () {

        Route::get('/dances', [APIJudgeController::class, 'getDances'])
            ->name('api.dances');

        Route::get('/votes', [APIJudgeController::class, 'getVotes'])
            ->name('api.votes.index');

        Route::post('/votes/{danceId}', [APIJudgeController::class, 'postVotes'])
            ->whereNumber('danceId')
            ->name('api.votes.store');

        Route::post('/status', [APIJudgeController::class, 'postStatus'])
            ->name('api.status');
    });
});
