<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Malico\Teams\Http\Controllers\CurrentTeamController;
use Malico\Teams\Http\Controllers\TeamInvitationController;

Route::group(['middleware' => ['auth']], function () {
    Volt::route('/teams', 'teams.index')->name('teams.index');
    Volt::route('/teams/create', 'teams.create')->name('teams.create');

    Volt::route('/team', 'teams.show')->name('teams.show');
    Volt::route('/team/members', 'teams.members')->name('teams.members');

    Volt::route('/team-invitations/{invitation}/accept', 'teams.accept-invitation')
        ->middleware(['auth', 'signed'])
        ->name('team-invitations.accept');

    Route::put('/current-team', CurrentTeamController::class)->name('current-team.update');
});

// Public invitation URL - handles redirects
Route::get('/team-invitations/{invitation}', [TeamInvitationController::class, 'show'])
    ->middleware(['signed'])
    ->name('team-invitations.show');
