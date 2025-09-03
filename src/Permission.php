<?php

namespace Malico\Teams;

class Permission
{
    /**
     * Check if permissions array contains a specific permission.
     */
    public static function hasPermission(array $permissions, string $permission): bool
    {
        foreach ($permissions as $perm) {
            if ($perm === $permission) {
                return true;
            }

            // Superadmin check: * permission grants access to everything
            if ($perm === '*' || $perm === '*:*') {
                return true;
            }

            // Wildcard support: users:*, users:create
            if (str_ends_with((string) $perm, '*')) {
                $base = rtrim((string) $perm, '*');
                if (str_starts_with($permission, $base)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if a role has a specific permission.
     */
    public static function roleHasPermission(Role $role, string $permission): bool
    {
        return self::hasPermission($role->permissions, $permission);
    }

    /**
     * Check if any of the given roles has a specific permission.
     */
    public static function anyRoleHasPermission(array $roles, string $permission): bool
    {
        foreach ($roles as $role) {
            if (self::roleHasPermission($role, $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all permissions that match a wildcard pattern from a role.
     */
    public static function getMatchingPermissions(Role $role, string $pattern): array
    {
        $matching = [];

        foreach ($role->permissions as $permission) {
            if (self::hasPermission([$permission], $pattern) || self::hasPermission([$pattern], $permission)) {
                $matching[] = $permission;
            }
        }

        return $matching;
    }
}
