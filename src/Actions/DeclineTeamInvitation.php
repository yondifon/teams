<?php

namespace Malico\Teams\Actions;

use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Malico\Teams\Contracts\DeclinesTeamInvitations;

class DeclineTeamInvitation implements DeclinesTeamInvitations
{
    /**
     * Decline the given team invitation.
     */
    public function decline($user, $invitation): void
    {
        $team = $invitation->team;

        Gate::forUser($user)->authorize('view', $team);

        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            $invitation->delete();
            throw ValidationException::withMessages([
                'invitation' => [__('This invitation has expired.')],
            ]);
        }

        $invitation->delete();
    }
}