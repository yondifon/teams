<?php

namespace Malico\Teams\Actions;

use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Malico\Teams\Contracts\InvitesTeamMembers;
use Malico\Teams\Contracts\SendsTeamInvitations;
use Malico\Teams\Events\InvitingTeamMember;
use Malico\Teams\Role as TeamsRole;
use Malico\Teams\Rules\Role;
use Malico\Teams\Teams;

class InviteTeamMember implements InvitesTeamMembers
{
    public function __construct(
        protected SendsTeamInvitations $sendsTeamInvitations
    ) {}

    /**
     * Invite a new team member to the given team.
     */
    public function invite($user, $team, string $email, TeamsRole|string|null $role = null)
    {
        Gate::forUser($user)->authorize('addTeamMember', $team);

        $this->validate($team, $email, $role);

        InvitingTeamMember::dispatch($team, $email, $role);

        $invitation = $team->invitations()->create([
            'email' => $email,
            'role' => $role,
            'invited_by_id' => $user->id,
            'expires_at' => Carbon::now()->addDays(Teams::invitationDuration()),
        ]);

        $this->sendsTeamInvitations->send($invitation);

        return $invitation;
    }

    /**
     * Validate the invite member operation.
     */
    protected function validate($team, string $email, TeamsRole|string|null $role): void
    {
        $role = $role instanceof TeamsRole ? $role->key : $role;

        Validator::make([
            'email' => $email,
            'role' => $role,
        ], $this->rules($team), [
            'email.unique' => __('This user has already been invited to the team.'),
        ])->after(
            $this->ensureUserIsNotAlreadyOnTeam($team, $email)
        )->validateWithBag('addTeamMember');
    }

    /**
     * Get the validation rules for inviting a team member.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    protected function rules($team): array
    {
        return array_filter([
            'email' => [
                'required', 'email',
                Rule::unique(Teams::teamInvitationModel())->where(function (Builder $query) use ($team): void {
                    $query->where('team_id', $team->id);
                }),
            ],
            'role' => Teams::hasRoles()
                ? ['required', 'string', new Role]
                : null,
        ]);
    }

    /**
     * Ensure that the user is not already on the team.
     */
    protected function ensureUserIsNotAlreadyOnTeam($team, string $email): Closure
    {
        return function ($validator) use ($team, $email): void {
            $validator->errors()->addIf(
                $team->hasUserWithEmail($email),
                'email',
                __('This user already belongs to the team.')
            );
        };
    }
}
