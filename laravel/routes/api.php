<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that API is required.
|
*/

// API Routes
$apiPrefix = 'api/v1';
Route::group(['prefix' => $apiPrefix], function () {
    Route::get('/competition', 'API\APICompetitionController@getCompetition');
    Route::get('/adjudicators', 'API\APICompetitionController@getAdjudicators');
});
Route::group(['prefix' => $apiPrefix, 'middleware' => 'APIAuth'], function () {
    Route::post('/login', 'API\APIJudgeLoginController@postLogin');
    Route::get('/dances', 'API\APIJudgeController@getDances');
    Route::get('/votes', 'API\APIJudgeController@getVotes');
    Route::post('/votes/{danceId}', 'API\APIJudgeController@postVotes');
    Route::post('/status', 'API\APIJudgeController@postStatus');
});