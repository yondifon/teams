<?php

namespace App\Actions\Teams;

use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class DeclineTeamInvitation
{
    /**
     * Decline the given team invitation.
     */
    public function decline(User $user, TeamInvitation $invitation): void
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