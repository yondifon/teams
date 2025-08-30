<?php

namespace Malico\Teams;

use Illuminate\Database\Eloquent\Model;
use Malico\Teams\Contracts\AddsTeamMembers;
use Malico\Teams\Contracts\CreatesTeams;
use Malico\Teams\Contracts\DeletesTeams;
use Malico\Teams\Contracts\InvitesTeamMembers;
use Malico\Teams\Contracts\RemovesTeamMembers;
use Malico\Teams\Contracts\SendsTeamInvitations;
use Malico\Teams\Contracts\UpdatesTeamNames;

class Teams
{
    /**
     * The roles that are available to assign to users.
     *
     * @var array
     */
    public static $roles = [];

    /**
     * The permissions that exist within the application.
     *
     * @var array
     */
    public static $permissions = [];

    /**
     * The user model that should be used by Teams.
     *
     * @var string
     */
    public static $userModel = 'App\\Models\\User';

    /**
     * The team model that should be used by Teams.
     *
     * @var string
     */
    public static $teamModel = 'App\\Models\\Team';

    /**
     * The membership model that should be used by Teams.
     *
     * @var string
     */
    public static $membershipModel = 'App\\Models\\Membership';

    /**
     * The team invitation model that should be used by Teams.
     *
     * @var string
     */
    public static $teamInvitationModel = 'App\\Models\\TeamInvitation';

    /**
     * The number of days team invitations are valid for.
     *
     * @var int
     */
    public static $invitationDuration = 7;

    /**
     * Determine if Teams has registered roles.
     *
     * @return bool
     */
    public static function hasRoles()
    {
        return count(static::$roles) > 0;
    }

    /**
     * Get all available roles.
     *
     * @return array
     */
    public static function getRoles()
    {
        return static::$roles;
    }

    /**
     * Find the role with the given key.
     *
     * @return \Malico\Teams\Role
     */
    public static function findRole(string $key)
    {
        return static::$roles[$key] ?? null;
    }

    /**
     * Define a role.
     *
     * @return \Malico\Teams\Role
     */
    public static function role(string $key, string $name, array $permissions)
    {
        static::$permissions = collect(array_merge(static::$permissions, $permissions))
            ->unique()
            ->sort()
            ->values()
            ->all();

        return tap(new Role($key, $name, $permissions), function ($role) use ($key): void {
            static::$roles[$key] = $role;
        });
    }

    /**
     * Determine if any permissions have been registered.
     *
     * @return bool
     */
    public static function hasPermissions()
    {
        return count(static::$permissions) > 0;
    }

    /**
     * Return the permissions in the given list that are actually defined permissions for the application.
     *
     * @return array
     */
    public static function validPermissions(array $permissions)
    {
        return array_values(array_intersect($permissions, static::$permissions));
    }

    /**
     * Determine if a given user model utilizes the "HasTeams" trait.
     *
     * @param  \Illuminate\Database\Eloquent\Model
     * @return bool
     */
    public static function userHasTeamFeatures($user)
    {
        return array_key_exists(HasTeams::class, class_uses_recursive($user)) ||
                method_exists($user, 'currentTeam');
    }

    /**
     * Find a user instance by the given ID.
     *
     * @param  int  $id
     * @return mixed
     */
    public static function findUserByIdOrFail($id)
    {
        return static::newUserModel()->where('id', $id)->firstOrFail();
    }

    /**
     * Find a user instance by the given email address or fail.
     *
     * @return mixed
     */
    public static function findUserByEmailOrFail(string $email)
    {
        return static::newUserModel()->where('email', $email)->firstOrFail();
    }

    /**
     * Get the name of the user model used by the application.
     *
     * @return string
     */
    public static function userModel()
    {
        return static::$userModel;
    }

    /**
     * Get a new instance of the user model.
     *
     * @return mixed
     */
    public static function newUserModel()
    {
        $model = static::userModel();

        return new $model;
    }

    /**
     * Specify the user model that should be used by Teams.
     *
     * @return static
     */
    public static function useUserModel(string $model)
    {
        static::$userModel = $model;

        return new static;
    }

    /**
     * Get the name of the team model used by the application.
     *
     * @return string
     */
    public static function teamModel()
    {
        return static::$teamModel;
    }

    /**
     * Get a new instance of the team model.
     *
     * @return mixed
     */
    public static function newTeamModel()
    {
        $model = static::teamModel();

        return new $model;
    }

    /**
     * Specify the team model that should be used by Teams.
     *
     * @return static
     */
    public static function useTeamModel(string $model)
    {
        static::$teamModel = $model;

        return new static;
    }

    /**
     * Get the name of the membership model used by the application.
     *
     * @return string
     */
    public static function membershipModel()
    {
        return static::$membershipModel;
    }

    /**
     * Specify the membership model that should be used by Teams.
     *
     * @return static
     */
    public static function useMembershipModel(string $model)
    {
        static::$membershipModel = $model;

        return new static;
    }

    /**
     * Get the name of the team invitation model used by the application.
     *
     * @return string
     */
    public static function teamInvitationModel()
    {
        return static::$teamInvitationModel;
    }

    /**
     * Specify the team invitation model that should be used by Teams.
     *
     * @return static
     */
    public static function useTeamInvitationModel(string $model)
    {
        static::$teamInvitationModel = $model;

        return new static;
    }

    /**
     * Register a class / callback that should be used to create teams.
     *
     * @return void
     */
    public static function createTeamsUsing(string $class)
    {
        return app()->singleton(CreatesTeams::class, $class);
    }

    /**
     * Register a class / callback that should be used to update team names.
     *
     * @return void
     */
    public static function updateTeamNamesUsing(string $class)
    {
        return app()->singleton(UpdatesTeamNames::class, $class);
    }

    /**
     * Register a class / callback that should be used to add team members.
     *
     * @return void
     */
    public static function addTeamMembersUsing(string $class)
    {
        return app()->singleton(AddsTeamMembers::class, $class);
    }

    /**
     * Register a class / callback that should be used to invite team members.
     *
     * @return void
     */
    public static function inviteTeamMembersUsing(string $class)
    {
        return app()->singleton(InvitesTeamMembers::class, $class);
    }

    /**
     * Register a class / callback that should be used to send team invitations.
     *
     * @return void
     */
    public static function sendTeamInvitationsUsing(string $class)
    {
        return app()->singleton(SendsTeamInvitations::class, $class);
    }

    /**
     * Register a class / callback that should be used to accept team invitations.
     *
     * @return void
     */
    public static function acceptTeamInvitationsUsing(string $class)
    {
        return app()->singleton(\Malico\Teams\Contracts\AcceptsTeamInvitations::class, $class);
    }

    /**
     * Register a class / callback that should be used to decline team invitations.
     *
     * @return void
     */
    public static function declineTeamInvitationsUsing(string $class)
    {
        return app()->singleton(\Malico\Teams\Contracts\DeclinesTeamInvitations::class, $class);
    }

    /**
     * Register a class / callback that should be used to update team member roles.
     *
     * @return void
     */
    public static function updateTeamMemberRolesUsing(string $class)
    {
        return app()->singleton(\Malico\Teams\Contracts\UpdatesTeamMemberRoles::class, $class);
    }

    /**
     * Register a class / callback that should be used to validate team deletion.
     *
     * @return void
     */
    public static function validateTeamDeletionUsing(string $class)
    {
        return app()->singleton(\Malico\Teams\Contracts\ValidatesTeamDeletion::class, $class);
    }

    /**
     * Register a class / callback that should be used to remove team members.
     *
     * @return void
     */
    public static function removeTeamMembersUsing(string $class)
    {
        return app()->singleton(RemovesTeamMembers::class, $class);
    }

    /**
     * Register a class / callback that should be used to delete teams.
     *
     * @return void
     */
    public static function deleteTeamsUsing(string $class)
    {
        return app()->singleton(DeletesTeams::class, $class);
    }

    /**
     * Get the number of days team invitations are valid for.
     *
     * @return int
     */
    public static function invitationDuration()
    {
        return static::$invitationDuration;
    }

    /**
     * Set the number of days team invitations are valid for.
     *
     * @return static
     */
    public static function invitationDurationDays(int $days)
    {
        static::$invitationDuration = $days;

        return new static;
    }
}
