<?php

namespace Malico\Teams\Actions;

use Illuminate\Support\Facades\Mail;
use Malico\Teams\Contracts\SendsTeamInvitations;
use Malico\Teams\Mail\TeamInvitation;

class SendTeamInvitation implements SendsTeamInvitations
{
    /**
     * Send a team invitation.
     */
    public function send($invitation): void
    {
        Mail::to($invitation->email)->send(new TeamInvitation($invitation));
    }
}
