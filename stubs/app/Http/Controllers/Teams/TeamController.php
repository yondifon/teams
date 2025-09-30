<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Malico\Teams\Contracts\AddsTeamMembers;
use Malico\Teams\Contracts\CreatesTeams;
use Malico\Teams\Contracts\DeletesTeams;
use Malico\Teams\Contracts\UpdatesTeamNames;
use Malico\Teams\Teams;

class TeamController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return Inertia::render('teams/index', [
            'teams' => $user->allTeams(),
            'currentTeam' => $user->currentTeam,
        ]);
    }

    public function create()
    {
        return Inertia::render('teams/create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $team = app(CreatesTeams::class)->create(
            $request->user(),
            $request->only('name')
        );

        return redirect()->route('teams.show', $team);
    }

    public function show()
    {
        $user = auth()->user();
        $team = $user->currentTeam;

        if (! $team) {
            return redirect()->route('teams.create');
        }

        return Inertia::render('teams/show', [
            'team' => $team->load('owner'),
            'permissions' => [
                'canUpdateTeam' => Gate::check('updateTeamName', $team),
                'canDeleteTeam' => Gate::check('delete', $team),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $team = $request->user()->currentTeam;

        if (! $team) {
            return redirect()->route('teams.create');
        }

        Gate::authorize('updateTeamName', $team);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        app(UpdatesTeamNames::class)->update(
            $request->user(),
            $team,
            $request->only('name')
        );

        return back()->with('success', 'Team updated successfully.');
    }

    public function destroy(Request $request)
    {
        $team = $request->user()->currentTeam;

        if (! $team) {
            return redirect()->route('teams.create');
        }

        Gate::authorize('delete', $team);

        app(DeletesTeams::class)->delete($request->user(), $team);

        return redirect()->route('dashboard');
    }

    public function showAcceptInvitation($invitationId)
    {
        $model = Teams::teamInvitationModel();
        $invitation = $model::whereKey($invitationId)->firstOrFail();

        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            $invitation->delete();

            return Inertia::render('teams/invitation-expired');
        }

        if (! auth()->check()) {
            return Inertia::render('teams/accept-invitation', [
                'invitation' => $invitation->load('team'),
            ]);
        }

        if (auth()->user()->email !== $invitation->email) {
            return Inertia::render('teams/wrong-user', [
                'invitation' => $invitation->load('team'),
                'expectedEmail' => $invitation->email,
                'currentEmail' => auth()->user()->email,
            ]);
        }

        return Inertia::render('teams/accept-invitation', [
            'invitation' => $invitation->load('team'),
        ]);
    }

    public function acceptInvitation(Request $request, $invitationId)
    {
        $model = Teams::teamInvitationModel();
        $invitation = $model::whereKey($invitationId)->firstOrFail();

        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            $invitation->delete();

            return redirect('/')->with('error', __('This invitation has expired.'));
        }

        if (! auth()->check() || auth()->user()->email !== $invitation->email) {
            return redirect()->back()->with('error', __('You are not authorized to accept this invitation.'));
        }

        app(AddsTeamMembers::class)->add(
            $invitation->team->owner,
            $invitation->team,
            $invitation->email,
            $invitation->role
        );

        $invitation->delete();

        return redirect(config('teams.home', '/dashboard'))->with(
            'success',
            __('Great! You have accepted the invitation to join the :team team.', ['team' => $invitation->team->name])
        );
    }

    public function declineInvitation(Request $request, $invitationId)
    {
        $model = Teams::teamInvitationModel();
        $invitation = $model::whereKey($invitationId)->firstOrFail();

        if (auth()->check() && auth()->user()->email === $invitation->email) {
            $invitation->delete();
        }

        return redirect('/')->with('message', __('You have declined the team invitation.'));
    }
}
