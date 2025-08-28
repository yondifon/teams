<?php

namespace Malico\Teams\Http\Controllers\Inertia;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Malico\Teams\Actions\UpdateTeamMemberRole;
use Malico\Teams\Contracts\AddsTeamMembers;
use Malico\Teams\Contracts\InvitesTeamMembers;
use Malico\Teams\Contracts\RemovesTeamMembers;
use Malico\Teams\Teams;

class TeamMemberController extends Controller
{
    /**
     * Add a new team member to a team.
     *
     * @param  int  $teamId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $teamId)
    {
        $team = Teams::newTeamModel()->findOrFail($teamId);

        if (Teams::sendsTeamInvitations()) {
            app(InvitesTeamMembers::class)->invite(
                $request->user(),
                $team,
                $request->email ?: '',
                $request->role
            );
        } else {
            app(AddsTeamMembers::class)->add(
                $request->user(),
                $team,
                $request->email ?: '',
                $request->role
            );
        }

        return back(303);
    }

    /**
     * Update the given team member's role.
     *
     * @param  int  $teamId
     * @param  int  $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $teamId, $userId)
    {
        app(UpdateTeamMemberRole::class)->update(
            $request->user(),
            Teams::newTeamModel()->findOrFail($teamId),
            $userId,
            $request->role
        );

        return back(303);
    }

    /**
     * Remove the given user from the given team.
     *
     * @param  int  $teamId
     * @param  int  $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, $teamId, $userId)
    {
        $team = Teams::newTeamModel()->findOrFail($teamId);

        app(RemovesTeamMembers::class)->remove(
            $request->user(),
            $team,
            $user = Teams::findUserByIdOrFail($userId)
        );

        if ($request->user()->id === $user->id) {
            return redirect(config('teams.home', '/dashboard'));
        }

        return back(303);
    }
}
