<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes (Laravel 5.3)
|--------------------------------------------------------------------------
*/

// (opcjonalnie) standardowe trasy logowania/rejestracji/resetu dla zwykłych użytkowników
// Jeśli nie używasz – usuń te dwie linie.
//Auth::routes();
// Route::get('/home', 'HomeController@index')->name('home');

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'admin'], function () {
    // logowanie (bez middleware)
    Route::get('login',  'Admin\LoginController@showLogin');
    Route::post('login', 'Admin\LoginController@postLogin');

    // wylogowanie: POST (zamiast starego GET /admin/logout)
    Route::post('logout', 'Admin\DashboardController@logout')->name('admin.logout');

    // reszta tylko dla zalogowanych adminów
    Route::group(['middleware' => 'adminAuth'], function () {
        Route::get('/', 'Admin\DashboardController@showDashboard');

        Route::get('chooseTournament',  'Admin\DashboardController@showTournamentChooser');
        Route::post('chooseTournament', 'Admin\DashboardController@postTournamentChooser');

        Route::get('password/{userId}/{flag?}',  'Admin\DashboardController@showChangePasswordForm');
        Route::post('password/{userId}/{flag?}', 'Admin\DashboardController@postChangePassword');

        Route::get('savePasswordAll', 'Admin\DashboardController@savePasswordAll');

        // PROGRAM
        Route::group(['prefix' => 'program'], function () {
            Route::get('/',                        'Admin\DashboardController@showProgram');
            Route::get('saveCurrentProgram',       'Admin\DashboardController@saveCurrentProgram');
            Route::post('postAdditionalRound',     'Admin\DashboardController@postAdditionalRound');
            Route::post('postAddedRound',          'Admin\DashboardController@postAddedRound');
            Route::get('editProgram/{cmd?}',       'Admin\DashboardController@editProgram');
            Route::get('saveParameters',           'Admin\DashboardController@saveParameters');
            Route::get('newProgram',               'Admin\DashboardController@newProgram');
            Route::get('selectedCategories/{type?}','Admin\DashboardController@selectedCategories');
            Route::post('postSelectedCategories/{type?}', 'Admin\DashboardController@postSelectedCategories');
            Route::post('linkProgram',             'Admin\DashboardController@linkProgram');
            Route::post('/',                        'Admin\DashboardController@postSelectProgram');
            Route::post('temp',                     'Admin\DashboardController@postFinalProgram');
        });

        // ROUND
        Route::get('round',                           'Admin\DashboardController@showCurrentRound');
        Route::post('round',                          'Admin\DashboardController@postCloseRound');
        Route::get('roundFromDb/{dbRoundId}',         'Admin\DashboardController@showRound');
        Route::get('round/forceCloseDance/{roundId}', 'Admin\DashboardController@forceCloseDance');
        Route::post('round/undoRound',                'Admin\DashboardController@undoRound');
        Route::get('round/isNewRound',                'Admin\DashboardController@isNewRound');
        Route::get('round/roundResults/{dbRoundId}',  'Admin\DashboardController@getRoundResult');

        // REPORTS
        Route::get('report',                 'Admin\DashboardController@showReport');
        Route::post('postReport',            'Admin\DashboardController@postReport');
        Route::get('reportRoundData',        'Admin\DashboardController@reportRoundData');
        Route::get('reportCouples',          'Admin\DashboardController@reportCouples');
        Route::get('reportClubs',            'Admin\DashboardController@reportClubs');
        Route::get('reportOpenClubs',        'Admin\DashboardController@reportOpenClubs');
        Route::get('reportListsRange',       'Admin\DashboardController@reportListsRange');
        Route::post('postRanges',            'Admin\DashboardController@postRanges');
        Route::get('reportLists',            'Admin\DashboardController@reportLists');
        Route::get('reportCouplesConflict',  'Admin\DashboardController@reportCouplesConflict');
        Route::get('reportResults',          'Admin\DashboardController@reportResults');
        Route::get('reportResultsShort',     'Admin\DashboardController@reportResultsShort');
        Route::get('reportTrainee',          'Admin\DashboardController@reportTrainee');

        // generateReport był u Ciebie zdublowany — zostawiam jedną wersję GET i jedną POST
        Route::get('generateReport',         'Admin\DashboardController@generateReport');
        Route::post('report',                'Admin\DashboardController@generateReport');

        Route::get('reportSet/{roundId}',    'Admin\DashboardController@showReportSet');
        Route::post('reportSet',             'Admin\DashboardController@setReport');

        // UTILS / PANEL
        Route::get('utils/{userId}',         'Admin\DashboardController@showUtils');
        Route::post('postPanel',             'Admin\DashboardController@postPanel');
        Route::post('postPanelTable',        'Admin\DashboardController@postPanelTable');
        Route::post('postAddedJudge',        'Admin\DashboardController@postAddedJudge');
        Route::get('panel',                  'Admin\DashboardController@showPanel');
        Route::get('panelSet',               'Admin\DashboardController@panelSet');
        Route::get('panelSave',              'Admin\DashboardController@panelSave');

        // inne
        Route::get('help',                   'Admin\DashboardController@showHelp');
        Route::get('autocomplete',           'Admin\DashboardController@autocomplete');
    });
});

/*
|--------------------------------------------------------------------------
| WALL
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'wall'], function () {
    // logowanie (bez middleware)
    Route::get('login',  'Wall\LoginController@showLogin');
    Route::post('login', 'Wall\LoginController@postLogin');

    // wylogowanie: POST
    Route::post('logout', 'Wall\DashboardController@logout')->name('wall.logout');

    // chronione
    Route::group(['middleware' => 'wallAuth'], function () {
        Route::get('/',      'Wall\DashboardController@showConfig');
        Route::get('board',  'Wall\DashboardController@showDashboard');
    });
});
