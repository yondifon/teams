<?php

namespace Malico\Teams\Contracts;

interface RemovesTeamMembers
{
    /**
     * Remove the team member from the given team.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  mixed  $team
     * @param  mixed  $teamMember
     */
    public function remove($user, $team, $teamMember): void;
}
