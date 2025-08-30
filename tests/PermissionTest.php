<?php

namespace Malico\Teams\Tests;

use Malico\Teams\OwnerRole;
use Malico\Teams\Permission;
use Malico\Teams\Role;
use PHPUnit\Framework\TestCase;

class PermissionTest extends TestCase
{
    public function test_has_permission_with_exact_match()
    {
        $permissions = ['users:create', 'users:update', 'posts:delete'];

        $this->assertTrue(Permission::hasPermission($permissions, 'users:create'));
        $this->assertTrue(Permission::hasPermission($permissions, 'posts:delete'));
        $this->assertFalse(Permission::hasPermission($permissions, 'users:delete'));
    }

    public function test_has_permission_with_wildcard()
    {
        $permissions = ['users:*', 'admin:view'];

        $this->assertTrue(Permission::hasPermission($permissions, 'users:create'));
        $this->assertTrue(Permission::hasPermission($permissions, 'users:update'));
        $this->assertTrue(Permission::hasPermission($permissions, 'users:delete'));
        $this->assertTrue(Permission::hasPermission($permissions, 'users:anything'));
        $this->assertFalse(Permission::hasPermission($permissions, 'posts:create'));
        $this->assertTrue(Permission::hasPermission($permissions, 'admin:view'));
    }

    public function test_has_permission_with_superadmin()
    {
        $permissions = ['*'];

        $this->assertTrue(Permission::hasPermission($permissions, 'users:create'));
        $this->assertTrue(Permission::hasPermission($permissions, 'anything:at:all'));
        $this->assertTrue(Permission::hasPermission($permissions, 'admin:superpower'));
    }

    public function test_has_permission_with_empty_permissions()
    {
        $permissions = [];

        $this->assertFalse(Permission::hasPermission($permissions, 'users:create'));
        $this->assertFalse(Permission::hasPermission($permissions, 'anything'));
    }

    public function test_has_permission_with_complex_wildcards()
    {
        $permissions = ['admin:*', 'users:create', 'posts:*'];

        $this->assertTrue(Permission::hasPermission($permissions, 'admin:dashboard'));
        $this->assertTrue(Permission::hasPermission($permissions, 'admin:users:manage'));
        $this->assertTrue(Permission::hasPermission($permissions, 'users:create'));
        $this->assertFalse(Permission::hasPermission($permissions, 'users:update'));
        $this->assertTrue(Permission::hasPermission($permissions, 'posts:create'));
        $this->assertTrue(Permission::hasPermission($permissions, 'posts:update:all'));
    }

    public function test_role_has_permission()
    {
        $role = new Role('admin', 'Administrator', ['users:*', 'posts:create']);

        $this->assertTrue(Permission::roleHasPermission($role, 'users:create'));
        $this->assertTrue(Permission::roleHasPermission($role, 'users:update'));
        $this->assertTrue(Permission::roleHasPermission($role, 'posts:create'));
        $this->assertFalse(Permission::roleHasPermission($role, 'posts:delete'));
    }

    public function test_role_has_permission_with_owner_role()
    {
        $ownerRole = new OwnerRole;

        $this->assertTrue(Permission::roleHasPermission($ownerRole, 'users:create'));
        $this->assertTrue(Permission::roleHasPermission($ownerRole, 'anything:at:all'));
        $this->assertTrue(Permission::roleHasPermission($ownerRole, 'admin:superpower'));
    }

    public function test_any_role_has_permission()
    {
        $role1 = new Role('editor', 'Editor', ['posts:create', 'posts:update']);
        $role2 = new Role('viewer', 'Viewer', ['posts:view']);
        $role3 = new Role('admin', 'Admin', ['users:*']);

        $roles = [$role1, $role2, $role3];

        $this->assertTrue(Permission::anyRoleHasPermission($roles, 'posts:create'));
        $this->assertTrue(Permission::anyRoleHasPermission($roles, 'posts:view'));
        $this->assertTrue(Permission::anyRoleHasPermission($roles, 'users:create'));
        $this->assertFalse(Permission::anyRoleHasPermission($roles, 'comments:delete'));
    }

    public function test_any_role_has_permission_with_empty_roles()
    {
        $roles = [];

        $this->assertFalse(Permission::anyRoleHasPermission($roles, 'users:create'));
    }

    public function test_get_matching_permissions()
    {
        $role = new Role('admin', 'Admin', ['users:create', 'users:update', 'posts:delete', 'admin:view']);

        $matching = Permission::getMatchingPermissions($role, 'users:*');
        $this->assertCount(2, $matching);
        $this->assertContains('users:create', $matching);
        $this->assertContains('users:update', $matching);
        $this->assertNotContains('posts:delete', $matching);
    }

    public function test_get_matching_permissions_with_superadmin()
    {
        $ownerRole = new OwnerRole;

        $matching = Permission::getMatchingPermissions($ownerRole, 'users:create');
        $this->assertCount(1, $matching);
        $this->assertContains('*', $matching);
    }

    public function test_get_matching_permissions_with_no_matches()
    {
        $role = new Role('viewer', 'Viewer', ['posts:view']);

        $matching = Permission::getMatchingPermissions($role, 'users:*');
        $this->assertEmpty($matching);
    }

    public function test_wildcard_patterns_edge_cases()
    {
        $permissions = ['users:*', 'admin', 'posts:create:*'];

        // Test that 'users:' matches 'users:*'
        $this->assertTrue(Permission::hasPermission($permissions, 'users:'));

        // Test that exact match without colon works
        $this->assertTrue(Permission::hasPermission($permissions, 'admin'));

        // Test nested wildcards
        $this->assertTrue(Permission::hasPermission($permissions, 'posts:create:draft'));
        $this->assertTrue(Permission::hasPermission($permissions, 'posts:create:published'));
        $this->assertFalse(Permission::hasPermission($permissions, 'posts:update:draft'));
    }
}
