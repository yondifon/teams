<?php

namespace Malico\Teams\Tests\Actions;

use Illuminate\Validation\ValidationException;
use Malico\Teams\Actions\DeclineTeamInvitation;
use Malico\Teams\Tests\TestCase;

class DeclineTeamInvitationTest extends TestCase
{
    public function test_it_declines_team_invitation_successfully()
    {
        $team = $this->createTeam();
        $teamMember = $this->createUser();
        $invitation = $this->createTeamInvitation($team, [
            'email' => $teamMember->email,
        ]);

        (new DeclineTeamInvitation)->decline($teamMember, $invitation);

        $this->assertDatabaseMissing('team_invitations', ['id' => $invitation->id]);
    }

    public function test_it_rejects_invitation_for_different_email()
    {
        $team = $this->createTeam();
        $user = $this->createUser();
        $invitation = $this->createTeamInvitation($team, ['email' => 'different@example.com']);

        $this->expectException(ValidationException::class);

        (new DeclineTeamInvitation)->decline($user, $invitation);

        $this->assertDatabaseHas('team_invitations', ['id' => $invitation->id, 'email' => 'different@example.com']);
    }
}
