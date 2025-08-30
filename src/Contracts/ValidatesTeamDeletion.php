<?php

namespace Malico\Teams\Contracts;

interface ValidatesTeamDeletion
{
    /**
     * Validate that the team can be deleted by the given user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  mixed  $team
     */
    public function validate($user, $team): void;
}
