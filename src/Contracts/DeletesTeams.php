<?php

namespace Malico\Teams\Contracts;

interface DeletesTeams
{
    /**
     * Delete the given team.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  mixed  $team
     */
    public function delete($user, $team): void;
}
