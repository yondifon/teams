<?php

namespace Malico\Teams\Actions;

use Illuminate\Validation\ValidationException;
use Malico\Teams\Contracts\DeclinesTeamInvitations;

class DeclineTeamInvitation implements DeclinesTeamInvitations
{
    /**
     * Decline the given team invitation.
     */
    public function decline($user, $invitation): void
    {
        if ($user->email !== $invitation->email) {
            throw ValidationException::withMessages([
                'invitation' => [__('This invitation was sent to :email. Please sign in with that account or create one if you don\'t have it.', ['email' => $invitation->email])],
            ]);
        }

        $invitation->delete();
    }
}
