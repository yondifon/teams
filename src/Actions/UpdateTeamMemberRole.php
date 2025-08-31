<?php

namespace Malico\Teams\Actions;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Malico\Teams\Contracts\UpdatesTeamMemberRoles;
use Malico\Teams\Events\TeamMemberUpdated;
use Malico\Teams\Rules\Role;
use Malico\Teams\Teams;

class UpdateTeamMemberRole implements UpdatesTeamMemberRoles
{
    /**
     * Update the role for the given team member.
     */
    public function update($user, $team, $teamMemberId, string $role): void
    {
        Gate::forUser($user)->authorize('updateTeamMember', $team);

        Validator::make(['role' => $role], [
            'role' => ['required', 'string', new Role],
        ])->validate();

        $team->users()->updateExistingPivot($teamMemberId, [
            'role' => $role,
        ]);

        TeamMemberUpdated::dispatch($team->fresh(), Teams::findUserByIdOrFail($teamMemberId));
    }
}
