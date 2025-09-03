<?php

namespace Malico\Teams\Contracts;

interface AcceptsTeamInvitations
{
    /**
     * Accept the given team invitation.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     */
    public function accept($user, mixed $invitation): void;
}
