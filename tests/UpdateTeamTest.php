<?php

namespace Malico\Teams\Tests;

use App\Actions\Teams\CreateTeam;
use App\Actions\Teams\UpdateTeamName;
use App\Models\Team;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Malico\Teams\Teams;
use Malico\Teams\Tests\Fixtures\TeamPolicy;
use Malico\Teams\Tests\Fixtures\User;

class UpdateTeamTest extends OrchestraTestCase
{
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        Gate::policy(Team::class, TeamPolicy::class);
        Teams::useUserModel(User::class);
    }

    public function test_team_name_can_be_updated()
    {
        $team = $this->createTeam();

        $action = new UpdateTeamName;

        $action->update($team->owner, $team, ['name' => 'Test Team Updated']);

        $this->assertSame('Test Team Updated', $team->fresh()->name);
    }

    public function test_name_is_required()
    {
        $this->expectException(ValidationException::class);

        $team = $this->createTeam();

        $action = new UpdateTeamName;

        $action->update($team->owner, $team, ['name' => '']);
    }

    protected function createTeam()
    {
        $action = new CreateTeam;

        $user = User::forceCreate([
            'name' => 'Taylor Otwell',
            'email' => 'taylor@laravel.com',
            'password' => 'secret',
        ]);

        return $action->create($user, ['name' => 'Test Team']);
    }
}
