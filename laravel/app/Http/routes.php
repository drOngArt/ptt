<?php

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

get('/admin/login', 'Admin\LoginController@showLogin');
post('/admin/login', 'Admin\LoginController@postLogin');
get('/admin/chooseTournament', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@showTournamentChooser']);
post('/admin/chooseTournament', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postTournamentChooser']);
get('/admin', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@showDashboard']);
get('/admin/logout', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@logout']);
get('/admin/password/{userId}/{flag?}', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@showChangePasswordForm']);
get('/admin/savePasswordAll', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@savePasswordAll']);
post('/admin/password/{userId}/{flag?}', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postChangePassword']);
get('/admin/program', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@showProgram']);
get('/admin/program/saveCurrentProgram', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@saveCurrentProgram']);
post('/admin/program/postAdditionalRound', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postAdditionalRound']);
post('/admin/program/postAddedRound', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postAddedRound']);
get('/admin/program/editProgram/{cmd?}', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@editProgram']);
get('/admin/program/saveParameters', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@saveParameters']);
get('/admin/program/newProgram', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@newProgram']);
get('/admin/program/selectedCategories/{type?}', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@selectedCategories']);
post('/admin/program/postSelectedCategories/{type?}', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postSelectedCategories']);
post('/admin/program/linkProgram', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@linkProgram']);
post('/admin/program', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postSelectProgram']);
post('/admin/program/temp', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postFinalProgram']);
get('/admin/round', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@showCurrentRound']);
get('/admin/roundFromDb/{dbRoundId}', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@showRound']);
get('/admin/help', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@showHelp']);
post('/admin/round', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postCloseRound']);
get('/admin/report', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@showReport']);
post('/admin/postReport', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postReport']);
get('/admin/reportRoundData', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@reportRoundData']);
get('/admin/reportCouples', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@reportCouples']);
get('/admin/reportClubs', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@reportClubs']);
get('/admin/reportOpenClubs', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@reportOpenClubs']);
get('/admin/reportListsRange', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@reportListsRange']);
post('/admin/postRanges', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postRanges']);
get('/admin/reportLists', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@reportLists']);
get('/admin/reportCouplesConflict', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@reportCouplesConflict']);
get('/admin/reportResults', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@reportResults']);
get('/admin/reportResultsShort', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@reportResultsShort']);
get('/admin/reportTrainee', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@reportTrainee']);
post('/admin/report', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@generateReport']);
get('/admin/generateReport', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@generateReport']);
get('/admin/reportSet/{roundId}', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@showReportSet']);
post('/admin/reportSet', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@setReport']);
post('/admin/reportSet', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@setReport']);
get('/admin/round/forceCloseDance/{roundId}', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@forceCloseDance']);
post('/admin/round/undoRound', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@undoRound']);
get('/admin/round/isNewRound', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@isNewRound']);
get('/admin/round/roundResults/{dbRoundId}', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@getRoundResult']);
get('/admin/utils/{userId}', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@showUtils']);
post('/admin/postPanel', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postPanel']);
post('/admin/postPanelTable', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postPanelTable']);
post('/admin/postAddedJudge', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@postAddedJudge']);
get('/admin/panel', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@showPanel']);
get('/admin/panelSet', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@panelSet']);
get('/admin/panelSave', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@panelSave']);
get('/admin/autocomplete', ['middleware' => 'adminAuth', 'uses' => 'Admin\DashboardController@autocomplete']);

//wall, display, 
$wallPrefix = '/wall';

get($wallPrefix.'/login', 'Wall\LoginController@showLogin');
post($wallPrefix.'/login', 'Wall\LoginController@postLogin');
get($wallPrefix, ['middleware' => 'wallAuth', 'uses' => 'Wall\DashboardController@showConfig']);
get($wallPrefix.'/board',['middleware' => 'wallAuth', 'uses' => 'Wall\DashboardController@showDashboard']);
get($wallPrefix.'/logout', ['middleware' => 'wallAuth', 'uses' => 'Wall\DashboardController@logout']);

//API Routes
$apiPrefix = 'api/v1';
Route::group(['prefix' => $apiPrefix], function(){
    get('/competition', 'API\APICompetitionController@getCompetition');
    get('/adjudicators', 'API\APICompetitionController@getAdjudicators');
});
Route::group(['prefix' => $apiPrefix, 'middleware' => 'APIAuth'], function(){
    post('/login', 'API\APIJudgeLoginController@postLogin');
    get('/dances', 'API\APIJudgeController@getDances');
    get('/votes', 'API\APIJudgeController@getVotes');
    post('/votes/{danceId}', 'API\APIJudgeController@postVotes');
    post('/status', 'API\APIJudgeController@postStatus');
});