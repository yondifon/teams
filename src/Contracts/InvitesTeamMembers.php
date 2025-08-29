<?php

namespace Malico\Teams\Contracts;

interface InvitesTeamMembers
{
    /**
     * Invite a new team member to the given team.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  mixed  $team
     * @param  string  $email
     * @param  string|null  $role
     * @return void
     */
    public function invite($user, $team, string $email, ?string $role = null): void;
}
