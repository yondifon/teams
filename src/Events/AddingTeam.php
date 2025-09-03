<?php

namespace Malico\Teams\Events;

use Illuminate\Foundation\Events\Dispatchable;

class AddingTeam
{
    use Dispatchable;

    /**
     * Create a new event instance.
     */
    public function __construct(public mixed $owner) {}
}
