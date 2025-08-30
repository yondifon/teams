<?php

namespace Malico\Teams\Tests;

use Malico\Teams\OwnerRole;
use Malico\Teams\Role;
use Malico\Teams\Teams;

class HasTeamsTest extends TestCase
{
    public function test_determines_if_team_is_current_team()
    {
        $user = $this->createUser();
        $team = $this->createTeam(['user_id' => $user->id]);

        $user->current_team_id = $team->id;
        $user->setRelation('currentTeam', $team);

        $this->assertTrue($user->isCurrentTeam($team));
    }

    public function test_determines_if_team_is_not_current_team()
    {
        $user = $this->createUser();
        $team1 = $this->createTeam(['user_id' => $user->id]);
        $team2 = $this->createTeam(['user_id' => $user->id]);

        $user->current_team_id = $team1->id;
        $user->setRelation('currentTeam', $team1);

        $this->assertFalse($user->isCurrentTeam($team2));
    }

    public function test_determines_team_ownership()
    {
        $user = $this->createUser();
        $team = $this->createTeam(['user_id' => $user->id]);

        $this->assertTrue($user->ownsTeam($team));
    }

    public function test_determines_non_ownership()
    {
        $user = $this->createUser();
        $anotherUser = $this->createUser();
        $team = $this->createTeam(['user_id' => $anotherUser->id]);

        $this->assertFalse($user->ownsTeam($team));
    }

    public function test_handles_null_team_ownership()
    {
        $user = $this->createUser();

        $this->assertFalse($user->ownsTeam(null));
    }

    public function test_returns_owner_role_for_team_owner()
    {
        $user = $this->createUser();
        $team = $this->createTeam(['user_id' => $user->id]);

        $role = $user->teamRole($team);

        $this->assertInstanceOf(OwnerRole::class, $role);
    }

    public function test_returns_null_role_for_non_member()
    {
        $user = $this->createUser();
        $anotherUser = $this->createUser();
        $team = $this->createTeam(['user_id' => $anotherUser->id]);

        $role = $user->teamRole($team);

        $this->assertNull($role);
    }

    public function test_returns_team_permissions_for_owner()
    {
        $user = $this->createUser();
        $team = $this->createTeam(['user_id' => $user->id]);

        $permissions = $user->teamPermissions($team);

        $this->assertEquals(['*'], $permissions);
    }

    public function test_returns_empty_permissions_for_non_member()
    {
        $user = $this->createUser();
        $anotherUser = $this->createUser();
        $team = $this->createTeam(['user_id' => $anotherUser->id]);

        $permissions = $user->teamPermissions($team);

        $this->assertEquals([], $permissions);
    }

    public function test_checks_team_permission_for_owner()
    {
        $user = $this->createUser();
        $team = $this->createTeam(['user_id' => $user->id]);

        $this->assertTrue($user->hasTeamPermission($team, 'users:create'));
        $this->assertTrue($user->hasTeamPermission($team, 'anything:at:all'));
    }

    public function test_denies_team_permission_for_non_member()
    {
        $user = $this->createUser();
        $anotherUser = $this->createUser();
        $team = $this->createTeam(['user_id' => $anotherUser->id]);

        $this->assertFalse($user->hasTeamPermission($team, 'users:create'));
    }

    public function test_checks_team_permission_with_role_permissions()
    {
        $user = $this->createUser();
        $teamOwner = $this->createUser();
        $team = $this->createTeam(['user_id' => $teamOwner->id]);

        // Register a role with specific permissions
        Teams::role('editor', 'Editor', ['posts:create', 'posts:update']);

        // Add user to team with the editor role
        $team->users()->attach($user->id, ['role' => 'editor']);

        $this->assertTrue($user->hasTeamPermission($team, 'posts:create'));
        $this->assertTrue($user->hasTeamPermission($team, 'posts:update'));
        $this->assertFalse($user->hasTeamPermission($team, 'users:create'));
    }

    public function test_checks_team_permission_with_wildcard_permissions()
    {
        $user = $this->createUser();
        $teamOwner = $this->createUser();
        $team = $this->createTeam(['user_id' => $teamOwner->id]);

        // Register a role with wildcard permissions
        Teams::role('admin', 'Admin', ['posts:*', 'users:create']);

        // Add user to team with the admin role
        $team->users()->attach($user->id, ['role' => 'admin']);

        $this->assertTrue($user->hasTeamPermission($team, 'posts:create'));
        $this->assertTrue($user->hasTeamPermission($team, 'posts:update'));
        $this->assertTrue($user->hasTeamPermission($team, 'posts:delete'));
        $this->assertTrue($user->hasTeamPermission($team, 'users:create'));
        $this->assertFalse($user->hasTeamPermission($team, 'users:update'));
    }

    public function test_determines_team_membership_for_owner()
    {
        $user = $this->createUser();
        $team = $this->createTeam(['user_id' => $user->id]);

        $this->assertTrue($user->belongsToTeam($team));
    }

    public function test_determines_team_membership_for_member()
    {
        $user = $this->createUser();
        $teamOwner = $this->createUser();
        $team = $this->createTeam(['user_id' => $teamOwner->id]);

        // Add user as team member
        $team->users()->attach($user->id, ['role' => 'member']);

        $this->assertTrue($user->belongsToTeam($team));
    }

    public function test_determines_non_team_membership()
    {
        $user = $this->createUser();
        $teamOwner = $this->createUser();
        $team = $this->createTeam(['user_id' => $teamOwner->id]);

        $this->assertFalse($user->belongsToTeam($team));
    }

    public function test_handles_null_team_membership()
    {
        $user = $this->createUser();

        $this->assertFalse($user->belongsToTeam(null));
    }

    public function test_switches_team_for_member()
    {
        $user = $this->createUser();
        $teamOwner = $this->createUser();
        $team = $this->createTeam(['user_id' => $teamOwner->id]);

        // Add user as team member
        $team->users()->attach($user->id, ['role' => 'member']);

        $result = $user->switchTeam($team);

        $this->assertTrue($result);
        $this->assertEquals($team->id, $user->fresh()->current_team_id);
    }

    public function test_cannot_switch_to_non_member_team()
    {
        $user = $this->createUser();
        $teamOwner = $this->createUser();
        $team = $this->createTeam(['user_id' => $teamOwner->id]);

        $result = $user->switchTeam($team);

        $this->assertFalse($result);
    }
}
