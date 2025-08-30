<?php

namespace Malico\Teams\Contracts;

interface UpdatesTeamNames
{
    /**
     * Validate and update the given team's name.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  mixed  $team
     * @param  array<string, string>  $input
     */
    public function update($user, $team, array $input): void;
}
