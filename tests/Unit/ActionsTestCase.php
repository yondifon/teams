<?php

namespace Malico\Teams\Tests\Unit;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Malico\Teams\TeamsServiceProvider;
use Malico\Teams\Tests\TestCase;

abstract class ActionsTestCase extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            TeamsServiceProvider::class,
        ];
    }

    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create($attributes);
    }

    protected function createTeam(array $attributes = []): Team
    {
        return Team::factory()->create($attributes);
    }

    protected function createTeamInvitation(Team $team, array $attributes = []): TeamInvitation
    {
        return TeamInvitation::factory()->create(array_merge([
            'team_id' => $team->id,
            'email' => 'invited@example.com',
            'role' => 'member',
        ], $attributes));
    }
}
