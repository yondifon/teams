<?php

namespace Malico\Teams\Events;

use Illuminate\Foundation\Events\Dispatchable;

class RemovingTeamMember
{
    use Dispatchable;

    /**
     * Create a new event instance.
     *
     * @param  mixed  $team
     * @param  mixed  $user
     */
    public function __construct(public $team, public $user) {}
}
