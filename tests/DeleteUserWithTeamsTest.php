<?php

namespace Malico\Teams\Tests;

use App\Actions\Teams\CreateTeam;
use App\Actions\Teams\DeleteTeam;
use App\Actions\Teams\DeleteUser;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Malico\Teams\Teams;
use Malico\Teams\Tests\Fixtures\TeamPolicy;
use Malico\Teams\Tests\Fixtures\User;

class DeleteUserWithTeamsTest extends OrchestraTestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app)
    {
        parent::defineEnvironment($app);

        Gate::policy(Team::class, TeamPolicy::class);
        Teams::useUserModel(User::class);
    }

    public function test_user_can_be_deleted()
    {
        $team = $this->createTeam();
        $otherTeam = $this->createTeam();

        $otherTeam->users()->attach($team->owner, ['role' => null]);

        $this->assertSame(2, DB::table('teams')->count());
        $this->assertSame(1, DB::table('team_user')->count());

        copy(__DIR__.'/../stubs/app/Actions/Teams/DeleteUserWithTeams.php', $fixture = __DIR__.'/Fixtures/DeleteUser.php');

        require $fixture;

        $action = new DeleteUser(new DeleteTeam);

        $action->delete($team->owner);

        $this->assertNull($team->owner->fresh());
        $this->assertSame(1, DB::table('teams')->count());
        $this->assertSame(0, DB::table('team_user')->count());

        @unlink($fixture);
    }

    protected function createTeam()
    {
        $action = new CreateTeam;

        $user = User::forceCreate([
            'name' => Str::random(10),
            'email' => Str::random(10).'@laravel.com',
            'password' => 'secret',
        ]);

        return $action->create($user, ['name' => 'Test Team']);
    }

    protected function afterRefreshingDatabase()
    {
        Schema::create('personal_access_tokens', function ($table) {
            $table->id();
            $table->foreignId('tokenable_id');
            $table->string('tokenable_type');
        });
    }
}
