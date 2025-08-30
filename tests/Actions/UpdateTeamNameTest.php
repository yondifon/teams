<?php

namespace Malico\Teams\Tests\Actions;

use Illuminate\Validation\ValidationException;
use Malico\Teams\Actions\UpdateTeamName;
use Malico\Teams\Tests\TestCase;

class UpdateTeamNameTest extends TestCase
{
    public function test_it_updates_team_name_successfully()
    {
        $user = $this->createUser();
        $team = $this->createTeam(['user_id' => $user->id]);

        (new UpdateTeamName)->update($user, $team, ['name' => 'Updated Team Name']);

        $this->assertEquals('Updated Team Name', $team->fresh()->name);
    }

    public function test_it_validates_name_is_required()
    {
        $user = $this->createUser();
        $team = $this->createTeam(['user_id' => $user->id]);

        $this->expectException(ValidationException::class);

        (new UpdateTeamName)->update($user, $team, []);
    }

    public function test_it_validates_name_max_length()
    {
        $user = $this->createUser();
        $team = $this->createTeam(['user_id' => $user->id]);

        $this->expectException(ValidationException::class);

        (new UpdateTeamName)->update($user, $team, ['name' => str_repeat('a', 256)]);
    }

    public function test_it_requires_authorization_to_update_team()
    {
        $team = $this->createTeam();
        $team->users()->attach($user = $this->createUser());

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        (new UpdateTeamName)->update($user, $team, ['name' => 'Updated Team Name']);
    }

    public function test_validation_uses_correct_error_bag()
    {
        $team = $this->createTeam();

        try {
            (new UpdateTeamName)->update($team->owner, $team, ['name' => '']);
        } catch (ValidationException $e) {
            $this->assertEquals('updateTeamName', $e->errorBag);
        }
    }
}
