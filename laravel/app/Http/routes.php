<?php

use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/admin/login', 'Admin\LoginController@showLogin');
Route::post('/admin/login', 'Admin\LoginController@postLogin');
Route::get('/admin/chooseTournament', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@showTournamentChooser']);
Route::post('/admin/chooseTournament', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postTournamentChooser']);
Route::get('/admin', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@showDashboard']);
Route::get('/admin/logout', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@logout']);
Route::get('/admin/password/{userId}/{flag?}', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@showChangePasswordForm']);
Route::get('/admin/savePasswordAll', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@savePasswordAll']);
Route::post('/admin/password/{userId}/{flag?}', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postChangePassword']);
Route::get('/admin/program', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@showProgram']);
Route::get('/admin/program/saveCurrentProgram', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@saveCurrentProgram']);
Route::post('/admin/program/postAdditionalRound', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postAdditionalRound']);
Route::post('/admin/program/postAddedRound', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postAddedRound']);
Route::get('/admin/program/editProgram/{cmd?}', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@editProgram']);
Route::get('/admin/program/saveParameters', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@saveParameters']);
Route::get('/admin/program/newProgram', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@newProgram']);
Route::get('/admin/program/selectedCategories/{type?}', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@selectedCategories']);
Route::post('/admin/program/postSelectedCategories/{type?}', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postSelectedCategories']);
Route::post('/admin/program/linkProgram', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@linkProgram']);
Route::post('/admin/program', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postSelectProgram']);
Route::post('/admin/program/temp', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postFinalProgram']);
Route::get('/admin/round', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@showCurrentRound']);
Route::get('/admin/roundFromDb/{dbRoundId}', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@showRound']);
Route::get('/admin/help', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@showHelp']);
Route::post('/admin/round', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postCloseRound']);
Route::get('/admin/report', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@showReport']);
Route::post('/admin/postReport', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postReport']);
Route::get('/admin/reportRoundData', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@reportRoundData']);
Route::get('/admin/reportCouples', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@reportCouples']);
Route::get('/admin/reportClubs', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@reportClubs']);
Route::get('/admin/reportOpenClubs', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@reportOpenClubs']);
Route::get('/admin/reportListsRange', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@reportListsRange']);
Route::post('/admin/postRanges', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postRanges']);
Route::get('/admin/reportLists', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@reportLists']);
Route::get('/admin/reportCouplesConflict', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@reportCouplesConflict']);
Route::get('/admin/reportResults', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@reportResults']);
Route::get('/admin/reportResultsShort', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@reportResultsShort']);
Route::get('/admin/reportTrainee', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@reportTrainee']);
Route::post('/admin/report', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@generateReport']);
Route::get('/admin/generateReport', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@generateReport']);
Route::get('/admin/reportSet/{roundId}', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@showReportSet']);
Route::post('/admin/reportSet', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@setReport']);
Route::post('/admin/reportSet', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@setReport']);
Route::get('/admin/round/forceCloseDance/{roundId}', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@forceCloseDance']);
Route::post('/admin/round/undoRound', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@undoRound']);
Route::get('/admin/round/isNewRound', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@isNewRound']);
Route::get('/admin/round/roundResults/{dbRoundId}', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@getRoundResult']);
Route::get('/admin/utils/{userId}', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@showUtils']);
Route::post('/admin/postPanel', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postPanel']);
Route::post('/admin/postPanelTable', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postPanelTable']);
Route::post('/admin/postAddedJudge', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postAddedJudge']);
Route::get('/admin/panel', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@showPanel']);
Route::get('/admin/panelSet', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@panelSet']);
Route::get('/admin/panelSave', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@panelSave']);
Route::get('/admin/autocomplete', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@autocomplete']);

//wall, display, 
$wallPrefix = '/wall';

Route::get($wallPrefix.'/login', 'Wall\LoginController@showLogin');
Route::post($wallPrefix.'/login', 'Wall\LoginController@postLogin');
Route::get($wallPrefix, ['middleware' => 'wallAuth', 'uses' => 'Wall\DashboardController@showConfig']);
Route::get($wallPrefix.'/board',['middleware' => 'wallAuth', 'uses' => 'Wall\DashboardController@showDashboard']);
Route::get($wallPrefix.'/logout', ['middleware' => 'wallAuth', 'uses' => 'Wall\DashboardController@logout']);

//API Routes
$apiPrefix = 'api/v1';
Route::group(['prefix' => $apiPrefix], function(){
    Route::get('/competition', 'API\APICompetitionController@getCompetition');
    Route::get('/adjudicators', 'API\APICompetitionController@getAdjudicators');
});
Route::group(['prefix' => $apiPrefix, 'middleware' => 'APIAuth'], function(){
    Route::post('/login', 'API\APIJudgeLoginController@postLogin');
    Route::get('/dances', 'API\APIJudgeController@getDances');
    Route::get('/votes', 'API\APIJudgeController@getVotes');
    Route::post('/votes/{danceId}', 'API\APIJudgeController@postVotes');
    Route::post('/status', 'API\APIJudgeController@postStatus');
});