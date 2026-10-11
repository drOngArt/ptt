
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Wall\DashboardController as WallDashboardController;

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {

    // AUTH
    Route::get('login', [AdminLoginController::class, 'showLogin'])
        ->name('login');

    Route::post('login', [AdminLoginController::class, 'postLogin'])
        ->name('login.submit');

    Route::post('logout', [AdminDashboardController::class, 'logout'])
        ->name('logout');

    Route::middleware('adminAuth')->group(function () {

        // DASHBOARD
        Route::get('/', [AdminDashboardController::class, 'showDashboard'])
            ->name('dashboard');

        // TOURNAMENT
        Route::get('chooseTournament', [AdminDashboardController::class, 'showTournamentChooser'])
            ->name('tournament.choose');

        Route::post('chooseTournament', [AdminDashboardController::class, 'postTournamentChooser'])
            ->name('tournament.choose.submit');

        // PASSWORD
        Route::get('password/{userId}/{flag?}', [AdminDashboardController::class, 'showChangePasswordForm'])
            ->whereNumber('userId')
            ->name('password.edit');

        Route::post('password/{userId}/{flag?}', [AdminDashboardController::class, 'postChangePassword'])
            ->whereNumber('userId')
            ->name('password.update');

        Route::get('savePasswordAll', [AdminDashboardController::class, 'savePasswordAll'])
            ->name('password.save-all');

        /*
        |--------------------------------------------------------------------------
        | PROGRAM
        |--------------------------------------------------------------------------
        */

        Route::prefix('program')->name('program.')->group(function () {

            Route::get('/', [AdminDashboardController::class, 'showProgram'])
                ->name('index');

            Route::get('saveCurrentProgram', [AdminDashboardController::class, 'saveCurrentProgram'])
                ->name('save-current');

            Route::post('postAdditionalRound', [AdminDashboardController::class, 'postAdditionalRound'])
                ->name('additional-round');

            Route::post('postAddedRound', [AdminDashboardController::class, 'postAddedRound'])
                ->name('added-round');

            Route::get('editProgram/{cmd?}', [AdminDashboardController::class, 'editProgram'])
                ->name('edit');

            Route::get('saveParameters', [AdminDashboardController::class, 'saveParameters'])
                ->name('save-parameters');

            Route::get('newProgram', [AdminDashboardController::class, 'newProgram'])
                ->name('new');

            Route::get('selectedCategories/{type?}', [AdminDashboardController::class, 'selectedCategories'])
                ->name('categories');

            Route::post('postSelectedCategories/{type?}', [AdminDashboardController::class, 'postSelectedCategories'])
                ->name('categories.submit');

            Route::post('linkProgram', [AdminDashboardController::class, 'linkProgram'])
                ->name('link');

            Route::post('/', [AdminDashboardController::class, 'postSelectProgram'])
                ->name('select');

            Route::post('temp', [AdminDashboardController::class, 'postFinalProgram'])
                ->name('final');
        });

        /*
        |--------------------------------------------------------------------------
        | ROUND
        |--------------------------------------------------------------------------
        */

        Route::get('round', [AdminDashboardController::class, 'showCurrentRound'])
            ->name('round.index');

        Route::post('round', [AdminDashboardController::class, 'postCloseRound'])
            ->name('round.close');

        Route::get('roundFromDb/{dbRoundId}', [AdminDashboardController::class, 'showRound'])
            ->whereNumber('dbRoundId')
            ->name('round.from-db');

        Route::get('round/forceCloseDance/{roundId}', [AdminDashboardController::class, 'forceCloseDance'])
            ->whereNumber('roundId')
            ->name('round.force-close');

        Route::post('round/undoRound', [AdminDashboardController::class, 'undoRound'])
            ->name('round.undo');

        Route::get('round/isNewRound', [AdminDashboardController::class, 'isNewRound'])
            ->name('round.is-new');

        Route::get('round/roundResults/{dbRoundId}', [AdminDashboardController::class, 'getRoundResult'])
            ->whereNumber('dbRoundId')
            ->name('round.results');

        /*
        |--------------------------------------------------------------------------
        | REPORTS
        |--------------------------------------------------------------------------
        */

        Route::get('report', [AdminDashboardController::class, 'showReport'])
            ->name('report.index');

        Route::post('postReport', [AdminDashboardController::class, 'postReport'])
            ->name('report.submit');

        Route::get('reportRoundData', [AdminDashboardController::class, 'reportRoundData'])
            ->name('report.round-data');

        Route::get('reportCouples', [AdminDashboardController::class, 'reportCouples'])
            ->name('report.couples');

        Route::get('reportClubs', [AdminDashboardController::class, 'reportClubs'])
            ->name('report.clubs');

        Route::get('reportOpenClubs', [AdminDashboardController::class, 'reportOpenClubs'])
            ->name('report.open-clubs');

        Route::get('reportListsRange', [AdminDashboardController::class, 'reportListsRange'])
            ->name('report.lists-range');

        Route::post('postRanges', [AdminDashboardController::class, 'postRanges'])
            ->name('report.ranges.submit');

        Route::get('reportLists', [AdminDashboardController::class, 'reportLists'])
            ->name('report.lists');

        Route::get('reportCouplesConflict', [AdminDashboardController::class, 'reportCouplesConflict'])
            ->name('report.couples-conflict');

        Route::get('reportResults', [AdminDashboardController::class, 'reportResults'])
            ->name('report.results');

        Route::get('reportResultsShort', [AdminDashboardController::class, 'reportResultsShort'])
            ->name('report.results-short');

        Route::get('reportTrainee', [AdminDashboardController::class, 'reportTrainee'])
            ->name('report.trainee');

        Route::get('generateReport', [AdminDashboardController::class, 'generateReport'])
            ->name('report.generate');

        Route::post('report', [AdminDashboardController::class, 'generateReport'])
            ->name('report.generate.submit');

        Route::get('reportSet/{roundId}', [AdminDashboardController::class, 'showReportSet'])
            ->whereNumber('roundId')
            ->name('report.set');

        Route::post('reportSet', [AdminDashboardController::class, 'setReport'])
            ->name('report.set.submit');

        /*
        |--------------------------------------------------------------------------
        | UTILS / PANEL
        |--------------------------------------------------------------------------
        */

        Route::get('utils/{userId}', [AdminDashboardController::class, 'showUtils'])
            ->whereNumber('userId')
            ->name('utils');

        Route::post('postPanel', [AdminDashboardController::class, 'postPanel'])
            ->name('panel.submit');

        Route::post('postPanelTable', [AdminDashboardController::class, 'postPanelTable'])
            ->name('panel.table.submit');

        Route::post('postAddedJudge', [AdminDashboardController::class, 'postAddedJudge'])
            ->name('panel.judge.submit');

        Route::get('panel', [AdminDashboardController::class, 'showPanel'])
            ->name('panel.index');

        Route::get('panelSet', [AdminDashboardController::class, 'panelSet'])
            ->name('panel.set');

        Route::post('panelSet', [AdminDashboardController::class, 'panelSet'])
            ->name('panel.set.submit');

        Route::get('panelSave', [AdminDashboardController::class, 'panelSave'])
            ->name('panel.save');

        /*
        |--------------------------------------------------------------------------
        | OTHER
        |--------------------------------------------------------------------------
        */

        Route::get('help', [AdminDashboardController::class, 'showHelp'])
            ->name('help');

        Route::get('autocomplete', [AdminDashboardController::class, 'autocomplete'])
            ->name('autocomplete');
    });
});

/*
|--------------------------------------------------------------------------
| WALL
|--------------------------------------------------------------------------
*/

// Zachowujemy dotychczasowy redirect z tokenem.
Route::get('/wall', function () {
    return redirect()->route('wall.config', [
        'token' => config('services.wall.token'),
    ]);
})->name('wall.index');

Route::prefix('wall')->name('wall.')->group(function () {

    Route::get('{token}', [WallDashboardController::class, 'showConfig'])
        ->name('config');

    Route::get('{token}/board', [WallDashboardController::class, 'showDashboard'])
        ->name('board');
});
