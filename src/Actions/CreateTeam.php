<?php

namespace Malico\Teams\Actions;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Malico\Teams\Contracts\CreatesTeams;
use Malico\Teams\Events\AddingTeam;
use Malico\Teams\Teams;

class CreateTeam implements CreatesTeams
{
    /**
     * Validate and create a new team for the given user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array<string, string>  $input
     */
    public function create($user, array $input)
    {
        Gate::forUser($user)->authorize('create', Teams::newTeamModel());

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'personal_team' => ['boolean', function ($attribute, $value, $fail) use ($user): void {
                $hasPersonal = Teams::teamModel()::query()
                    ->where('personal_team', true)
                    ->where('user_id', $user->id)
                    ->exists();

                if ($value && $hasPersonal) {
                    $fail('You may not create a personal team.')->translate();
                }
            }],
        ])->validateWithBag('createTeam');

        AddingTeam::dispatch($user);

        $user->switchTeam($team = $user->ownedTeams()->create([
            'name' => $input['name'],
            'personal_team' => $input['personal_team'] ?? false,
        ]));

        return $team;
    }
}
