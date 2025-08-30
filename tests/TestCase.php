<?php

namespace Malico\Teams\Tests;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Policies\TeamPolicy;
use Illuminate\Support\Facades\Gate;
use Malico\Teams\TeamsServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithWorkbench;

    protected function defineEnvironment($app)
    {
        Gate::guessPolicyNamesUsing(function ($class) {
            $baseName = class_basename($class);

            return match ($class) {
                Team::class => TeamPolicy::class,
                default => "App\\Policies\\{$baseName}Policy",
            };
        });
    }

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
