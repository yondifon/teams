<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use Malico\Teams\Http\Controllers\CurrentTeamController;

Route::group(['middleware' => ['auth']], function () {
    Volt::route('/teams', 'teams.create')->name('teams.index');
    Volt::route('/teams/create', 'teams.create')->name('teams.create');
    Volt::route('/team/settings', 'teams.show')->name('teams.show');
    Volt::route('/team/members', 'teams.members')->name('teams.members');

    Route::put('/current-team', CurrentTeamController::class)->name('current-team.update');
});

Volt::route('/team-invitations/{invitation}', 'teams.accept-invitation')
    ->middleware(['auth', 'signed'])
    ->name('team-invitations.accept');
