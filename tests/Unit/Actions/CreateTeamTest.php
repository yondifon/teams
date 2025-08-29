<?php

namespace Malico\Teams\Tests\Unit\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Malico\Teams\Actions\CreateTeam;
use Malico\Teams\Events\AddingTeam;
use Malico\Teams\Tests\Unit\ActionsTestCase;

class CreateTeamTest extends ActionsTestCase
{
    public function test_it_creates_a_team_successfully()
    {
        $user = $this->createUser();

        $team = (new CreateTeam)->create($user, ['name' => 'New Team']);

        $this->assertNotNull($team);
        $this->assertEquals('New Team', $team->name);
        $this->assertEquals($user->id, $team->user_id);
        $this->assertFalse($team->personal_team);
        $this->assertEquals($team->id, $user->fresh()->current_team_id);
    }

    public function test_it_can_create_personal_team()
    {
        $user = $this->createUser();

        $team = (new CreateTeam)->create($user, ['name' => 'New Team', 'personal_team' => true]);

        $this->assertNotNull($team);
        $this->assertEquals('New Team', $team->name);
        $this->assertEquals($user->id, $team->user_id);
        $this->assertTrue($team->personal_team);
        $this->assertEquals($team->id, $user->fresh()->current_team_id);
    }

    public function test_it_validates_personal_team()
    {
        $user = $this->createUser();
        $privateTeam = $this->createTeam([
            'name' => 'Private Team',
            'user_id' => $user->id,
            'personal_team' => true,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('You may not create a personal team.');

        (new CreateTeam)->create($user, ['name' => 'New Team', 'personal_team' => true]);
    }

    public function test_it_dispatches_adding_team_event()
    {
        Event::fake();
        $user = $this->createUser();

        (new CreateTeam)->create($user, ['name' => 'New Team']);

        Event::assertDispatched(AddingTeam::class, function ($event) use ($user) {
            return $event->owner->id === $user->id;
        });
    }

    public function test_it_validates_team_name_is_required()
    {
        $user = $this->createUser();

        $this->expectException(ValidationException::class);

        (new CreateTeam)->create($user, []);
    }

    public function test_it_validates_team_name_max_length()
    {
        $user = $this->createUser();

        $this->expectException(ValidationException::class);

        (new CreateTeam)->create($user, ['name' => str_repeat('a', 256)]);
    }

    public function test_it_requires_authorization_to_create_team()
    {
        $user = $this->createUser();
        Gate::shouldReceive('forUser')
            ->with($user)
            ->once()
            ->andReturn(new class
            {
                public function authorize(...$args)
                {
                    throw new AuthorizationException('Call to undefined method class@anonymous::authorize()');
                }
            });

        $this->expectException(AuthorizationException::class);

        (new CreateTeam)->create($user, ['name' => 'New Team']);

        $this->assertDatabaseMissing('teams', [
            'name' => 'New Team',
            'user_id' => $user->id,
        ]);
    }

    public function test_it_switches_user_to_new_team()
    {
        $user = $this->createUser();
        $originalTeamId = $user->current_team_id;

        $team = (new CreateTeam)->create($user, ['name' => 'New Team']);

        $this->assertNotEquals($originalTeamId, $user->fresh()->current_team_id);
        $this->assertEquals($team->id, $user->fresh()->current_team_id);
    }
}
