<?php

namespace Malico\Teams\Tests\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Malico\Teams\Actions\RemoveTeamMember;
use Malico\Teams\Events\TeamMemberRemoved;
use Malico\Teams\Tests\TestCase;

class RemoveTeamMemberTest extends TestCase
{
    public function test_it_removes_team_member_successfully()
    {
        Event::fake();
        $user = $this->createUser();
        $team = $this->createTeam(['user_id' => $user->id]);
        $teamMember = $this->createUser();
        $team->users()->attach($teamMember);

        (new RemoveTeamMember)->remove($user, $team, $teamMember);

        $this->assertFalse($team->fresh()->hasUser($teamMember));
        Event::assertDispatched(TeamMemberRemoved::class);
    }

    public function test_it_allows_user_to_remove_themselves()
    {
        Event::fake();
        $user = $this->createUser();
        $team = $this->createTeam(['user_id' => $user->id]);
        $teamMember = $this->createUser();
        $team->users()->attach($teamMember);

        (new RemoveTeamMember)->remove($teamMember, $team, $teamMember);

        $this->assertFalse($team->fresh()->hasUser($teamMember));
    }

    public function test_it_prevents_team_owner_from_leaving()
    {
        $user = $this->createUser();
        $team = $this->createTeam(['user_id' => $user->id]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('You may not leave a team that you created.');

        (new RemoveTeamMember)->remove($user, $team, $user);
    }

    public function test_it_requires_authorization_to_remove_others()
    {
        $team = $this->createTeam();
        $team->users()->attach($user = $this->createUser());
        $team->users()->attach($teamMember = $this->createUser());

        $this->expectException(AuthorizationException::class);

        (new RemoveTeamMember)->remove($user, $team, $teamMember);
    }

    public function test_it_dispatches_team_member_removed_event()
    {
        Event::fake();
        $user = $this->createUser();
        $team = $this->createTeam(['user_id' => $user->id]);
        $teamMember = $this->createUser();
        $team->users()->attach($teamMember);

        (new RemoveTeamMember)->remove($user, $team, $teamMember);

        Event::assertDispatched(TeamMemberRemoved::class, function ($event) use ($team, $teamMember) {
            return $event->team->id === $team->id
                && $event->user->id === $teamMember->id;
        });
    }
}
