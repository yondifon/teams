<?php

namespace Malico\Teams\Contracts;

interface DeclinesTeamInvitations
{
    /**
     * Decline the given team invitation.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  mixed  $invitation
     * @return void
     */
    public function decline($user, $invitation): void;
}