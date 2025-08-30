<?php

namespace Malico\Teams\Contracts;

interface AcceptsTeamInvitations
{
    /**
     * Accept the given team invitation.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  mixed  $invitation
     */
    public function accept($user, $invitation): void;
}
