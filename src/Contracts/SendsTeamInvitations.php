<?php

namespace Malico\Teams\Contracts;

interface SendsTeamInvitations
{
    /**
     * Send a team invitation.
     */
    public function send($invitation): void;
}
