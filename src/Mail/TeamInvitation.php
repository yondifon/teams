<?php

namespace Malico\Teams\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class TeamInvitation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  \Malico\Teams\TeamInvitation  $invitation
     */
    public function __construct(public $invitation) {}

    /**
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.team-invitation', [
            'acceptUrl' => URL::signedRoute('team-invitations.accept', [
                'invitation' => $this->invitation,
            ]),
            'invitedByName' => $this->invitation->invitedBy?->name,
        ])->subject(__('You\'re invited to join :team', ['team' => $this->invitation->team->name]));
    }
}
