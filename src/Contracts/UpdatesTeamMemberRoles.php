<?php

namespace Malico\Teams\Contracts;

interface UpdatesTeamMemberRoles
{
    /**
     * Update the role for the given team member.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  mixed  $team
     * @param  int  $teamMemberId
     * @param  string  $role
     * @return void
     */
    public function update($user, $team, $teamMemberId, string $role): void;
}