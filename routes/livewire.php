<?php

use Illuminate\Support\Facades\Route;
use Malico\Teams\Http\Controllers\CurrentTeamController;
use Malico\Teams\Http\Controllers\Livewire\TeamController;
use Malico\Teams\Http\Controllers\TeamInvitationController;

Route::group(['middleware' => config('teams.middleware', ['web'])], function () {
    $authMiddleware = config('teams.guard')
        ? 'auth:'.config('teams.guard')
        : 'auth';

    $authSessionMiddleware = config('teams.auth_session', false)
        ? config('teams.auth_session')
        : null;

    Route::group(['middleware' => array_values(array_filter([$authMiddleware, $authSessionMiddleware]))], function () {
        Route::group(['middleware' => 'verified'], function () {
            // Teams...
            Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
            Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
            Route::put('/current-team', [CurrentTeamController::class, 'update'])->name('current-team.update');

            Route::get('/team-invitations/{invitation}', [TeamInvitationController::class, 'accept'])
                ->middleware(['signed'])
                ->name('team-invitations.accept');
        });
    });
});
