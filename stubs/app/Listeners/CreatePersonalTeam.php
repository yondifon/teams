<?php

namespace App\Listeners;

use Malico\Teams\Contracts\CreatesTeams;
use Illuminate\Auth\Events\Registered;

class CreatePersonalTeam
{
    public function __construct(
        protected CreatesTeams $createTeam
    ) {}

    public function handle(Registered $event): void
    {
        $this->createTeam->create($event->user, [
            'name' => explode(' ', $event->user->name, 2)[0]."'s Team",
        ]);
    }
}

