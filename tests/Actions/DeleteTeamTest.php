<?php

namespace Malico\Teams\Tests\Actions;

use Illuminate\Validation\ValidationException;
use Malico\Teams\Actions\DeleteTeam;
use Malico\Teams\Tests\TestCase;

class DeleteTeamTest extends TestCase
{
    public function test_it_deletes_team_successfully()
    {
        $user = $this->createUser();
        $team = $this->createTeam(['user_id' => $user->id]);
        $teamId = $team->id;

        app(DeleteTeam::class)->delete($team->owner, $team);

        $this->assertDatabaseMissing('teams', ['id' => $teamId]);
    }

    public function test_it_handles_team_with_members()
    {
        $user = $this->createUser();
        $team = $this->createTeam(['user_id' => $user->id]);
        $teamMember = $this->createUser();
        $team->users()->attach($teamMember);
        $teamId = $team->id;

        $this->assertDatabaseHas('team_user', ['team_id' => $teamId]);

        app(DeleteTeam::class)->delete($user, $team);

        $this->assertDatabaseMissing('teams', ['id' => $teamId]);
        $this->assertDatabaseMissing('team_user', ['team_id' => $teamId]);
    }

    public function test_it_handles_team_with_invitations()
    {
        $user = $this->createUser();
        $team = $this->createTeam(['user_id' => $user->id]);
        $this->createTeamInvitation($team);
        $teamId = $team->id;

        $this->assertDatabaseHas('team_invitations', ['team_id' => $teamId]);

        app(DeleteTeam::class)->delete($user, $team);

        $this->assertDatabaseMissing('teams', ['id' => $teamId]);
        $this->assertDatabaseMissing('team_invitations', ['team_id' => $teamId]);
    }

    public function test_it_prevents_deleting_personal_team()
    {
        $user = $this->createUser();
        $personalTeam = $this->createTeam([
            'name' => 'Personal Team',
            'user_id' => $user->id,
            'personal_team' => true,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('You may not delete your personal team.');

        app(DeleteTeam::class)->delete($user, $personalTeam);
    }

    public function test_deletion_uses_correct_error_bag()
    {
        $user = $this->createUser();
        $personalTeam = $this->createTeam([
            'name' => 'Personal Team',
            'user_id' => $user->id,
            'personal_team' => true,
        ]);

        try {
            app(DeleteTeam::class)->delete($user, $personalTeam);
        } catch (ValidationException $e) {
            $this->assertEquals('deleteTeam', $e->errorBag);
        }
    }
}
