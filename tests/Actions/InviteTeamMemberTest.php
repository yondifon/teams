<?php

namespace Malico\Teams\Tests\Actions;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Malico\Teams\Actions\InviteTeamMember;
use Malico\Teams\Events\InvitingTeamMember;
use Malico\Teams\Mail\TeamInvitation;
use Malico\Teams\Tests\TestCase;

class InviteTeamMemberTest extends TestCase
{
    public function test_it_invites_a_team_member_successfully()
    {
        Event::fake();
        Mail::fake();
        $user = $this->createUser();
        $team = $this->createTeam(['user_id' => $user->id]);

        (new InviteTeamMember)->invite($user, $team, 'newmember@example.com', 'member');

        $this->assertDatabaseHas('team_invitations', [
            'team_id' => $team->id,
            'email' => 'newmember@example.com',
            'role' => 'member',
        ]);

        Event::assertDispatched(InvitingTeamMember::class);
        Mail::assertSent(TeamInvitation::class);
    }

    public function test_it_validates_email_is_required()
    {
        $user = $this->createUser();
        $team = $this->createTeam(['user_id' => $user->id]);

        $this->expectException(ValidationException::class);

        (new InviteTeamMember)->invite($user, $team, '', 'member');
    }

    public function test_it_validates_email_format()
    {
        $user = $this->createUser();
        $team = $this->createTeam(['user_id' => $user->id]);

        $this->expectException(ValidationException::class);

        (new InviteTeamMember)->invite($user, $team, 'invalid-email', 'member');
    }

    public function test_it_prevents_duplicate_invitations()
    {
        $user = $this->createUser();
        $team = $this->createTeam(['user_id' => $user->id]);
        $this->createTeamInvitation($team, ['email' => 'duplicate@example.com']);

        $this->expectException(ValidationException::class);

        (new InviteTeamMember)->invite($user, $team, 'duplicate@example.com', 'member');
    }

    public function test_it_prevents_inviting_existing_team_members()
    {
        $user = $this->createUser();
        $team = $this->createTeam(['user_id' => $user->id]);
        $teamMember = $this->createUser();
        $team->users()->attach($teamMember);

        $this->expectException(ValidationException::class);

        (new InviteTeamMember)->invite($user, $team, $teamMember->email, 'member');
    }

    public function test_it_requires_authorization_to_add_team_member()
    {
        $user = $this->createUser();
        $team = $this->createTeam();

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        (new InviteTeamMember)->invite($user, $team, 'newmember@example.com', 'member');
    }
}
