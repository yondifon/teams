<?php

namespace App\Listeners;

use App\Actions\Teams\CreateTeam;
use Illuminate\Auth\Events\Registered;

class CreatePersonalTeam
{
    public function __construct(
        protected CreateTeam $createTeam
    ) {}

    public function handle(Registered $event): void
    {
        $this->createTeam->create($event->user, [
            'name' => explode(' ', $event->user->name, 2)[0]."'s Team",
        ]);
    }
}

