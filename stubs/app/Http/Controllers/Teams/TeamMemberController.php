<?php

namespace App\Http\Controllers\Teams;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Malico\Teams\Contracts\InvitesTeamMembers;
use Malico\Teams\Contracts\RemovesTeamMembers;
use Malico\Teams\Contracts\SendsTeamInvitations;
use Malico\Teams\Contracts\UpdatesTeamMemberRoles;
use Malico\Teams\Teams;

class TeamMemberController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $team = $user->currentTeam;

        if (! $team) {
            return redirect()->route('teams.create');
        }

        return Inertia::render('teams/members', [
            'team' => $team->load(['owner', 'users', 'invitations']),
            'roles' => Teams::getRoles(),
            'permissions' => [
                'canAddTeamMembers' => Gate::check('addTeamMember', $team),
                'canRemoveTeamMembers' => Gate::check('removeTeamMember', $team),
                'canUpdateTeamMembers' => Gate::check('updateTeamMember', $team),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $team = $request->user()->currentTeam;

        if (! $team) {
            return redirect()->route('teams.create');
        }

        Gate::authorize('addTeamMember', $team);

        $request->validate([
            'email' => 'required|email',
            'role' => 'nullable|string',
        ]);

        app(InvitesTeamMembers::class)->invite(
            $request->user(),
            $team,
            $request->email,
            $request->role
        );

        return back()->with('success', 'Team member invited successfully.');
    }

    public function update(Request $request, $userId)
    {
        $team = $request->user()->currentTeam;
        $user = Teams::userModel()::findOrFail($userId);

        if (! $team) {
            return redirect()->route('teams.create');
        }

        Gate::authorize('updateTeamMember', $team);

        $request->validate([
            'role' => 'required|string',
        ]);

        app(UpdatesTeamMemberRoles::class)->update(
            $request->user(),
            $team,
            $user,
            $request->role
        );

        return back()->with('success', 'Team member role updated successfully.');
    }

    public function destroy(Request $request, $userId)
    {
        $team = $request->user()->currentTeam;
        $user = Teams::userModel()::findOrFail($userId);

        if (! $team) {
            return redirect()->route('teams.create');
        }

        Gate::authorize('removeTeamMember', $team);

        app(RemovesTeamMembers::class)->remove(
            $request->user(),
            $team,
            $user
        );

        return back()->with('success', 'Team member removed successfully.');
    }

    public function resendInvitation(Request $request, $invitationId)
    {
        $team = $request->user()->currentTeam;

        if (! $team) {
            return redirect()->route('teams.create');
        }

        $invitation = $team->invitations()->findOrFail($invitationId);

        Gate::authorize('addTeamMember', $team);

        app(SendsTeamInvitations::class)->send($invitation);

        return back()->with('success', 'Invitation resent successfully.');
    }

    public function cancelInvitation(Request $request, $invitationId)
    {
        $team = $request->user()->currentTeam;

        if (! $team) {
            return redirect()->route('teams.create');
        }

        $invitation = $team->invitations()->findOrFail($invitationId);

        Gate::authorize('removeTeamMember', $team);

        $invitation->delete();

        return back()->with('success', 'Invitation cancelled successfully.');
    }
}
