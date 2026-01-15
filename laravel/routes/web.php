<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

use App\Http\Controllers\Wall\LoginController as WallLoginController;
use App\Http\Controllers\Wall\DashboardController as WallDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// (opcjonalnie) standardowe trasy logowania/rejestracji/resetu dla zwykłych użytkowników
// Auth::routes();
// Route::get('/home', [HomeController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {

    // logowanie (bez middleware)
    Route::get('login',  [AdminLoginController::class, 'showLogin']);
    Route::post('login', [AdminLoginController::class, 'postLogin']);

    // wylogowanie: POST
    Route::post('logout', [AdminDashboardController::class, 'logout'])->name('admin.logout');

    // reszta tylko dla zalogowanych adminów
    Route::middleware('adminAuth')->group(function () {

        Route::get('/', [AdminDashboardController::class, 'showDashboard']);

        Route::get('chooseTournament',  [AdminDashboardController::class, 'showTournamentChooser']);
        Route::post('chooseTournament', [AdminDashboardController::class, 'postTournamentChooser']);

        Route::get('password/{userId}/{flag?}',  [AdminDashboardController::class, 'showChangePasswordForm'])
            ->whereNumber('userId');
        Route::post('password/{userId}/{flag?}', [AdminDashboardController::class, 'postChangePassword'])
            ->whereNumber('userId');

        Route::get('savePasswordAll', [AdminDashboardController::class, 'savePasswordAll']);

        // PROGRAM
        Route::prefix('program')->group(function () {

            Route::get('/', [AdminDashboardController::class, 'showProgram']);

            Route::get('saveCurrentProgram',   [AdminDashboardController::class, 'saveCurrentProgram']);
            Route::post('postAdditionalRound', [AdminDashboardController::class, 'postAdditionalRound']);
            Route::post('postAddedRound',      [AdminDashboardController::class, 'postAddedRound']);

            Route::get('editProgram/{cmd?}', [AdminDashboardController::class, 'editProgram']);

            Route::get('saveParameters', [AdminDashboardController::class, 'saveParameters']);
            Route::get('newProgram',     [AdminDashboardController::class, 'newProgram']);

            Route::get('selectedCategories/{type?}', [AdminDashboardController::class, 'selectedCategories']);
            Route::post('postSelectedCategories/{type?}', [AdminDashboardController::class, 'postSelectedCategories']);

            Route::post('linkProgram', [AdminDashboardController::class, 'linkProgram']);

            Route::post('/',    [AdminDashboardController::class, 'postSelectProgram']);
            Route::post('temp', [AdminDashboardController::class, 'postFinalProgram']);
        });

        // ROUND
        Route::get('round',  [AdminDashboardController::class, 'showCurrentRound']);
        Route::post('round', [AdminDashboardController::class, 'postCloseRound']);

        Route::get('roundFromDb/{dbRoundId}', [AdminDashboardController::class, 'showRound'])
            ->whereNumber('dbRoundId');

        Route::get('round/forceCloseDance/{roundId}', [AdminDashboardController::class, 'forceCloseDance'])
            ->whereNumber('roundId');

        Route::post('round/undoRound',  [AdminDashboardController::class, 'undoRound']);
        Route::get('round/isNewRound',  [AdminDashboardController::class, 'isNewRound']);

        Route::get('round/roundResults/{dbRoundId}', [AdminDashboardController::class, 'getRoundResult'])
            ->whereNumber('dbRoundId');

        // REPORTS
        Route::get('report',      [AdminDashboardController::class, 'showReport']);
        Route::post('postReport', [AdminDashboardController::class, 'postReport']);

        Route::get('reportRoundData',       [AdminDashboardController::class, 'reportRoundData']);
        Route::get('reportCouples',         [AdminDashboardController::class, 'reportCouples']);
        Route::get('reportClubs',           [AdminDashboardController::class, 'reportClubs']);
        Route::get('reportOpenClubs',       [AdminDashboardController::class, 'reportOpenClubs']);
        Route::get('reportListsRange',      [AdminDashboardController::class, 'reportListsRange']);
        Route::post('postRanges',           [AdminDashboardController::class, 'postRanges']);
        Route::get('reportLists',           [AdminDashboardController::class, 'reportLists']);
        Route::get('reportCouplesConflict', [AdminDashboardController::class, 'reportCouplesConflict']);
        Route::get('reportResults',         [AdminDashboardController::class, 'reportResults']);
        Route::get('reportResultsShort',    [AdminDashboardController::class, 'reportResultsShort']);
        Route::get('reportTrainee',         [AdminDashboardController::class, 'reportTrainee']);

        // masz dwa routy pod "generateReport" – zostawiam jak było
        Route::get('generateReport', [AdminDashboardController::class, 'generateReport']);
        Route::post('report',        [AdminDashboardController::class, 'generateReport']);

        Route::get('reportSet/{roundId}', [AdminDashboardController::class, 'showReportSet'])
            ->whereNumber('roundId');
        Route::post('reportSet', [AdminDashboardController::class, 'setReport']);

        // UTILS / PANEL
        Route::get('utils/{userId}', [AdminDashboardController::class, 'showUtils'])
            ->whereNumber('userId');

        Route::post('postPanel',      [AdminDashboardController::class, 'postPanel']);
        Route::post('postPanelTable', [AdminDashboardController::class, 'postPanelTable']);
        Route::post('postAddedJudge', [AdminDashboardController::class, 'postAddedJudge']);

        Route::get('panel',     [AdminDashboardController::class, 'showPanel']);
        Route::get('panelSet',  [AdminDashboardController::class, 'panelSet']);
        Route::get('panelSave', [AdminDashboardController::class, 'panelSave']);

        // inne
        Route::get('help',         [AdminDashboardController::class, 'showHelp']);
        Route::get('autocomplete', [AdminDashboardController::class, 'autocomplete']);
    });
});

/*
|--------------------------------------------------------------------------
| WALL
|--------------------------------------------------------------------------
*/
Route::prefix('wall')->group(function () {

    // logowanie (bez middleware)
    Route::get('login',  [WallLoginController::class, 'showLogin']);
    Route::post('login', [WallLoginController::class, 'postLogin']);

    // wylogowanie: POST
    Route::post('logout', [WallDashboardController::class, 'logout'])->name('wall.logout');

    // chronione
    Route::middleware('wallAuth')->group(function () {
        Route::get('/',     [WallDashboardController::class, 'showConfig']);
        Route::get('board', [WallDashboardController::class, 'showDashboard']);
    });
});
