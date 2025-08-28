<?php

use Illuminate\Support\Facades\Route;
use Malico\Teams\Http\Controllers\CurrentTeamController;
use Malico\Teams\Http\Controllers\Inertia\TeamController;
use Malico\Teams\Http\Controllers\Inertia\TeamMemberController;
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
            Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
            Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
            Route::put('/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
            Route::delete('/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
            Route::put('/current-team', [CurrentTeamController::class, 'update'])->name('current-team.update');
            Route::post('/teams/{team}/members', [TeamMemberController::class, 'store'])->name('team-members.store');
            Route::put('/teams/{team}/members/{user}', [TeamMemberController::class, 'update'])->name('team-members.update');
            Route::delete('/teams/{team}/members/{user}', [TeamMemberController::class, 'destroy'])->name('team-members.destroy');

            Route::get('/team-invitations/{invitation}', [TeamInvitationController::class, 'accept'])
                ->middleware(['signed'])
                ->name('team-invitations.accept');

            Route::delete('/team-invitations/{invitation}', [TeamInvitationController::class, 'destroy'])
                ->name('team-invitations.destroy');
        });
    });
});
