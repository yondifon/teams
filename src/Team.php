<?php

namespace Malico\Teams;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

abstract class Team extends Model
{
    /**
     * Get the owner of the team.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Teams::userModel(), 'user_id');
    }

    /**
     * Get all of the team's users including its owner.
     *
     * @return \Illuminate\Support\Collection
     */
    public function allUsers()
    {
        return $this->users->merge([$this->owner]);
    }

    /**
     * Get all of the users that belong to the team.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(Teams::userModel(), Teams::membershipModel())
            ->withPivot(['role', 'invited_by_id'])
            ->withTimestamps()
            ->as('membership');
    }

    /**
     * Determine if the given user belongs to the team.
     *
     * @param  \App\Models\User  $user
     */
    public function hasUser($user): bool
    {
        if ($this->users->contains($user)) {
            return true;
        }

        return $user->ownsTeam($this);
    }

    /**
     * Determine if the given email address belongs to a user on the team.
     */
    public function hasUserWithEmail(string $email): bool
    {
        return $this->allUsers()->contains(fn ($user) => $user->email === $email);
    }

    /**
     * Determine if the given user has the given permission on the team.
     *
     * @param  \App\Models\User  $user
     * @param  string  $permission
     */
    public function userHasPermission($user, $permission): bool
    {
        return $user->hasTeamPermission($this, $permission);
    }

    /**
     * Get all of the pending user invitations for the team.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(Teams::teamInvitationModel());
    }

    /**
     * Remove the given user from the team.
     *
     * @param  \App\Models\User  $user
     */
    public function removeUser($user): void
    {
        if ($user->current_team_id === $this->id) {
            $user->forceFill([
                'current_team_id' => null,
            ])->save();
        }

        $this->users()->detach($user);
    }

    /**
     * Purge all of the team's resources.
     */
    public function purge(): void
    {
        $this->owner()->where('current_team_id', $this->id)
            ->update(['current_team_id' => null]);

        $this->users()->where('current_team_id', $this->id)
            ->update(['current_team_id' => null]);

        $this->users()->detach();

        $this->invitations()->delete();

        $this->delete();
    }
}
