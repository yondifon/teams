<?php

namespace Malico\Teams\Contracts;

use Malico\Teams\Role;

interface AddsTeamMembers
{
    /**
     * Add a new team member to the given team.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     */
    public function add($user, mixed $team, string $email, Role|string|null $role = null): void;
}
