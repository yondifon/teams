<?php

namespace Malico\Teams\Tests\Unit\Actions;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Malico\Teams\Actions\AcceptTeamInvitation;
use Malico\Teams\Events\TeamMemberAdded;
use Malico\Teams\Tests\Unit\ActionsTestCase;

class AcceptTeamInvitationTest extends ActionsTestCase
{
    public function test_it_accepts_team_invitation_successfully()
    {
        Event::fake();
        $team = $this->createTeam();
        $teamMember = $this->createUser();
        $invitation = $this->createTeamInvitation($team, ['email' => $teamMember->email]);

        (new AcceptTeamInvitation)->accept($teamMember, $invitation);

        $this->assertTrue($team->hasUser($teamMember));
        $this->assertDatabaseMissing('team_invitations', ['id' => $invitation->id]);
        Event::assertDispatched(TeamMemberAdded::class);
    }

    public function test_it_sets_current_team_if_user_has_none()
    {
        $team = $this->createTeam();
        $teamMember = $this->createUser();
        $invitation = $this->createTeamInvitation($team, ['email' => $teamMember->email]);
        $teamMember->update(['current_team_id' => null]);

        (new AcceptTeamInvitation)->accept($teamMember, $invitation);

        $this->assertEquals($team->id, $teamMember->fresh()->current_team_id);
    }

    public function test_it_rejects_expired_invitation()
    {
        $team = $this->createTeam();
        $teamMember = $this->createUser();
        $invitation = $this->createTeamInvitation($team, [
            'email' => $teamMember->email,
            'expires_at' => Carbon::yesterday(),
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('This invitation has expired.');

        (new AcceptTeamInvitation)->accept($teamMember, $invitation);
    }

    public function test_it_rejects_invitation_for_different_email()
    {
        $team = $this->createTeam();
        $teamMember = $this->createUser();
        $invitation = $this->createTeamInvitation($team, ['email' => 'different@example.com']);

        $this->expectException(ValidationException::class);

        (new AcceptTeamInvitation)->accept($teamMember, $invitation);
    }

    public function test_it_rejects_invitation_for_existing_team_member()
    {
        $team = $this->createTeam();
        $teamMember = $this->createUser();
        $team->users()->attach($teamMember);
        $invitation = $this->createTeamInvitation($team, ['email' => $teamMember->email]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('You are already a member of this team.');

        (new AcceptTeamInvitation)->accept($teamMember, $invitation);
    }
}
