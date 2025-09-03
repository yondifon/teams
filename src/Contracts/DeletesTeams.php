<?php

namespace Malico\Teams\Contracts;

interface DeletesTeams
{
    /**
     * Delete the given team.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     */
    public function delete($user, mixed $team): void;
}
