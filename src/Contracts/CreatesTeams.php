<?php

namespace Malico\Teams\Contracts;

interface CreatesTeams
{
    /**
     * Validate and create a new team for the given user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array<string, string>  $input
     * @return mixed
     */
    public function create($user, array $input);
}
