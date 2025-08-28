<?php

namespace Malico\Teams\Tests;

use App\Actions\Teams\CreateTeam;
use App\Actions\Teams\DeleteTeam;
use App\Models\Team;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Malico\Teams\Actions\ValidateTeamDeletion;
use Malico\Teams\Teams;
use Malico\Teams\Tests\Fixtures\TeamPolicy;
use Malico\Teams\Tests\Fixtures\User;

class DeleteTeamTest extends OrchestraTestCase
{
    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        Gate::policy(Team::class, TeamPolicy::class);
        Teams::useUserModel(User::class);
    }

    public function test_team_can_be_deleted()
    {
        $team = $this->createTeam();

        $action = new DeleteTeam;

        $action->delete($team);

        $this->assertNull($team->fresh());
    }

    public function test_team_deletion_can_be_validated()
    {
        Teams::useUserModel(User::class);

        $team = $this->createTeam();

        $action = new ValidateTeamDeletion;

        $action->validate($team->owner, $team);

        $this->assertTrue(true);
    }

    public function test_personal_team_cant_be_deleted()
    {
        $this->expectException(ValidationException::class);

        Teams::useUserModel(User::class);

        $team = $this->createTeam();

        $team->forceFill(['personal_team' => true])->save();

        $action = new ValidateTeamDeletion;

        $action->validate($team->owner, $team);
    }

    public function test_non_owner_cant_delete_team()
    {
        $this->expectException(AuthorizationException::class);

        Teams::useUserModel(User::class);

        $team = $this->createTeam();

        $action = new ValidateTeamDeletion;

        $action->validate(User::forceCreate([
            'name' => 'Adam Wathan',
            'email' => 'adam@laravel.com',
            'password' => 'secret',
        ]), $team);
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
