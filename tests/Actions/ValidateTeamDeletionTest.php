<?php

namespace Malico\Teams\Tests\Actions;

use Illuminate\Validation\ValidationException;
use Malico\Teams\Actions\ValidateTeamDeletion;
use Malico\Teams\Tests\TestCase;

class ValidateTeamDeletionTest extends TestCase
{
    public function test_it_validates_team_deletion_successfully()
    {
        $user = $this->createUser();
        $team = $this->createTeam(['user_id' => $user->id]);

        (new ValidateTeamDeletion)->validate($user, $team);

        $this->expectNotToPerformAssertions();
    }

    public function test_it_requires_authorization_to_delete_team()
    {
        $team = $this->createTeam();
        $team->users()->attach($user = $this->createUser());

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        (new ValidateTeamDeletion)->validate($user, $team);
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

        (new ValidateTeamDeletion)->validate($user, $personalTeam);
    }

    public function test_validation_uses_correct_error_bag()
    {
        $user = $this->createUser();
        $personalTeam = $this->createTeam([
            'name' => 'Personal Team',
            'user_id' => $user->id,
            'personal_team' => true,
        ]);

        try {
            (new ValidateTeamDeletion)->validate($user, $personalTeam);
        } catch (ValidationException $e) {
            $this->assertEquals('deleteTeam', $e->errorBag);
        }
    }

    public function test_it_allows_deleting_non_personal_team()
    {
        $user = $this->createUser();
        $regularTeam = $this->createTeam([
            'name' => 'Regular Team',
            'user_id' => $user->id,
            'personal_team' => false,
        ]);

        // Should not throw any exception
        (new ValidateTeamDeletion)->validate($user, $regularTeam);

        $this->expectNotToPerformAssertions();
    }
}
