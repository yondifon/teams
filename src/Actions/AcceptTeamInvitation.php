<?php

namespace Malico\Teams\Actions;

use Illuminate\Validation\ValidationException;
use Malico\Teams\Contracts\AcceptsTeamInvitations;
use Malico\Teams\Events\TeamMemberAdded;

class AcceptTeamInvitation implements AcceptsTeamInvitations
{
    /**
     * Accept the given team invitation.
     */
    public function accept($user, $invitation): void
    {
        $team = $invitation->team;

        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            $invitation->delete();
            throw ValidationException::withMessages([
                'invitation' => [__('This invitation has expired.')],
            ]);
        }

        if ($user->email !== $invitation->email) {
            throw ValidationException::withMessages([
                'invitation' => [__('This invitation was sent to :email. Please sign in with that account or create one if you don\'t have it.', ['email' => $invitation->email])],
            ]);
        }

        if ($team->hasUserWithEmail($user->email)) {
            throw ValidationException::withMessages([
                'invitation' => [__('You are already a member of this team.')],
            ]);
        }

        $team->users()->attach($user, [
            'role' => $invitation->role,
            'invited_by_id' => $invitation->invited_by_id,
        ]);

        $invitation->delete();

        if (! $user->currentTeam) {
            $user->forceFill([
                'current_team_id' => $team->id,
            ])->save();
        }

        TeamMemberAdded::dispatch($team, $user);
    }
}
