<?php

namespace Malico\Teams\Contracts;

interface DeclinesTeamInvitations
{
    /**
     * Decline the given team invitation.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     */
    public function decline($user, mixed $invitation): void;
}
