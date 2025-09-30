<?php

use App\Http\Controllers\Teams\TeamController;
use App\Http\Controllers\Teams\TeamMemberController;
use Illuminate\Support\Facades\Route;
use Malico\Teams\Http\Controllers\CurrentTeamController;
use Malico\Teams\Http\Controllers\TeamInvitationController;

Route::group(['middleware' => ['auth', 'verified']], function (): void {
    Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
    Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
    Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');

    Route::get('/team', [TeamController::class, 'show'])->name('teams.show');
    Route::put('/team', [TeamController::class, 'update'])->name('teams.update');
    Route::delete('/team', [TeamController::class, 'destroy'])->name('teams.destroy');

    Route::get('/team/members', [TeamMemberController::class, 'index'])->name('teams.members');
    Route::post('/team/members', [TeamMemberController::class, 'store'])->name('team-members.store');
    Route::put('/team/members/{user}', [TeamMemberController::class, 'update'])->name('team-members.update');
    Route::delete('/team/members/{user}', [TeamMemberController::class, 'destroy'])->name('team-members.destroy');
    Route::post('/team/invitations/{invitation}/resend', [TeamMemberController::class, 'resendInvitation'])->name('team-invitations.resend');
    Route::delete('/team/invitations/{invitation}', [TeamMemberController::class, 'cancelInvitation'])->name('team-invitations.cancel');

    Route::get('/team-invitations/{invitation}/accept', [TeamController::class, 'showAcceptInvitation'])
        ->middleware(['signed'])
        ->name('team-invitations.accept');

    Route::post('/team-invitations/{invitation}/accept', [TeamController::class, 'acceptInvitation'])
        ->middleware(['signed'])
        ->name('team-invitations.process-accept');

    Route::post('/team-invitations/{invitation}/decline', [TeamController::class, 'declineInvitation'])
        ->middleware(['signed'])
        ->name('team-invitations.decline');

    Route::delete('/team-invitations/{invitation}', [TeamInvitationController::class, 'destroy'])
        ->name('team-invitations.destroy');

    Route::put('/current-team', [CurrentTeamController::class, 'update'])->name('current-team.update');
});

// Public invitation URL - handles redirects
Route::get('/team-invitations/{invitation}', [TeamInvitationController::class, 'show'])
    ->middleware(['signed'])
    ->name('team-invitations.show');
