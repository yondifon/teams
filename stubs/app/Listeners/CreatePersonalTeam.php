<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Malico\Teams\Contracts\CreatesTeams;

class CreatePersonalTeam
{
    public function __construct(
        protected CreatesTeams $createTeam
    ) {}

    public function handle(Registered $event): void
    {
        $this->createTeam->create($event->user, [
            'name' => explode(' ', $event->user->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]);
    }
}
