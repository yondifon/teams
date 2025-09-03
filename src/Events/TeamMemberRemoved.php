<?php

namespace Malico\Teams\Events;

use Illuminate\Foundation\Events\Dispatchable;

class TeamMemberRemoved
{
    use Dispatchable;

    /**
     * Create a new event instance.
     */
    public function __construct(public mixed $team, public mixed $user) {}
}
