<?php

namespace Malico\Teams\Tests;

use Malico\Teams\Teams;

class TeamsTest extends OrchestraTestCase
{
    public function test_roles_can_be_registered()
    {
        Teams::$permissions = [];
        Teams::$roles = [];

        Teams::role('admin', 'Admin', [
            'read',
            'create',
        ])->description('Admin Description');

        Teams::role('editor', 'Editor', [
            'read',
            'update',
            'delete',
        ])->description('Editor Description');

        $this->assertTrue(Teams::hasPermissions());

        $this->assertEquals([
            'create',
            'delete',
            'read',
            'update',
        ], Teams::$permissions);
    }

    public function test_roles_can_be_json_serialized()
    {
        Teams::$permissions = [];
        Teams::$roles = [];

        $role = Teams::role('admin', 'Admin', [
            'read',
            'create',
        ])->description('Admin Description');

        $serialized = $role->jsonSerialize();

        $this->assertArrayHasKey('key', $serialized);
        $this->assertArrayHasKey('name', $serialized);
        $this->assertArrayHasKey('description', $serialized);
        $this->assertArrayHasKey('permissions', $serialized);
    }

    public function test_has_team_feature_will_always_return_false_when_team_is_not_enabled()
    {
        $this->assertFalse(Teams::hasTeamFeatures());
        $this->assertFalse(Teams::userHasTeamFeatures(new Fixtures\User));
        $this->assertFalse(Teams::userHasTeamFeatures(new Fixtures\Admin));
    }

    /**
     * @define-env defineHasTeamEnvironment
     */
    public function test_has_team_feature_can_be_determined_when_team_is_enabled()
    {
        $this->assertTrue(Teams::hasTeamFeatures());
        $this->assertTrue(Teams::userHasTeamFeatures(new Fixtures\User));
        $this->assertFalse(Teams::userHasTeamFeatures(new Fixtures\Admin));
    }
}
