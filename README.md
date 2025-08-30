# Laravel Teams

A Laravel package that provides team management functionality for multi-tenant applications. Create teams, manage members, send invitations, and handle role-based permissions with a simple, extensible API.

## Features

-   **Team Management**: Create, update, and delete teams with owner relationships
-   **Member Management**: Add and remove team members with role assignments
-   **Email Invitations**: Send team invitations via email with signed URLs
-   **Role-Based Permissions**: Flexible role system with custom permissions
-   **Personal Teams**: Support for personal teams alongside collaborative teams
-   **Current Team Context**: Switch between teams with user context management
-   **Event System**: Events for all team operations (adding, removing, inviting, etc.)
-   **Multi-Stack Support**: Livewire components included, Inertia.js support planned
-   **Authorization**: Built-in policies and gates for secure operations

## Requirements

-   PHP 8.2 or higher
-   Laravel 11.0 or 12.0

## Installation

Install the package via Composer:

```bash
composer require malico/teams
```

Run the installation command to set up the package:

```bash
php artisan teams:install
```

Available installation options:

```bash
php artisan teams:install --stack=livewire    # Install Livewire components
php artisan teams:install --stack=inertia     # Install Inertia.js components (coming soon)
php artisan teams:install --override          # Override auth files with team invitation support
```

The installation process will:

-   Publish database migrations for teams, memberships, and invitations
-   Copy model stubs (Team, TeamInvitation, Membership, User)
-   Install the TeamsServiceProvider
-   Copy policy and listener classes
-   Set up frontend components for your chosen stack
-   Add team routes to your application

## Configuration

### User Model Setup

Add the `HasTeams` trait to your User model:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Malico\Teams\HasTeams;

class User extends Authenticatable
{
    use HasTeams;

    // Your existing model code...
}
```

### Team Roles Configuration

Define team roles in your TeamsServiceProvider or AppServiceProvider:

```php
use Malico\Teams\Teams;

public function boot(): void
{
    Teams::role('admin', 'Administrator', [
        'team:read',
        'team:update',
        'team:invite-members',
        'team:remove-members',
    ])->description('Team administrator with management privileges');

    Teams::role('member', 'Member', [
        'team:read',
    ])->description('Standard team member with read access');
}
```

**Note**: Team owners automatically have all permissions (`['*']`) and don't need to be explicitly defined.

## Usage

### Creating Teams

```php
use Malico\Teams\Contracts\CreatesTeams;

// Create a regular team
$team = app(CreatesTeams::class)->create($user, [
    'name' => 'Development Team',
]);

// Create a personal team
$personalTeam = app(CreatesTeams::class)->create($user, [
    'name' => 'John\'s Personal Team',
    'personal_team' => true,
]);
```

### Managing Team Members

```php
use Malico\Teams\Contracts\InvitesTeamMembers;
use Malico\Teams\Contracts\AcceptsTeamInvitations;
use Malico\Teams\Contracts\DeclinesTeamInvitations;
use Malico\Teams\Contracts\AddsTeamMembers;
use Malico\Teams\Contracts\RemovesTeamMembers;

// Invite a team member (sends email)
app(InvitesTeamMembers::class)->invite($user, $team, 'developer@example.com', 'admin');

// Add a team member directly (if user exists)
app(AddsTeamMembers::class)->add($teamOwner, $team, 'existing@example.com', 'member');

// Remove a team member
app(RemovesTeamMembers::class)->remove($user, $team, $memberUser);

// Accept an invitation
app(AcceptsTeamInvitations::class)->accept($user, $teamInvitation);

// Decline an invitation
app(DeclinesTeamInvitations::class)->decline($user, $teamInvitation);
```

### Updating Teams

```php
use Malico\Teams\Contracts\UpdatesTeamNames;
use Malico\Teams\Contracts\UpdatesTeamMemberRoles;

// Update team name
app(UpdatesTeamNames::class)->update($user, $team, ['name' => 'New Team Name']);

// Update member role
app(UpdatesTeamMemberRoles::class)->update($user, $team, $memberId, 'admin');
```

### Checking Team Permissions

```php
// Check if user belongs to a team
if ($user->belongsToTeam($team)) {
    // User is a team member
}

// Check user's role on a team
if ($user->hasTeamRole($team, 'admin')) {
    // User is an admin on this team
}

// Check specific permissions
if ($user->hasTeamPermission($team, 'team:update')) {
    // User can update this team
}
```

### Working with Current Team

```php
// Get user's current team
$currentTeam = $user->currentTeam;

// Switch to a different team
$user->switchTeam($team);

// Get all user's teams (owned + member)
$teams = $user->allTeams();

// Get teams the user owns
$ownedTeams = $user->ownedTeams;

// Get teams where user is a member (not owner)
$memberTeams = $user->teams;

// Get user's personal team
$personalTeam = $user->personalTeam();

// Check if team is user's current team
if ($user->isCurrentTeam($team)) {
    // This is the active team
}
```

### Deleting Teams

```php
use Malico\Teams\Contracts\DeletesTeams;

// Delete a team (validates permissions first)
app(DeletesTeams::class)->delete($user, $team);
```

## Frontend Integration

This package supports Livewire with planned Inertia.js support. The installation command will scaffold the appropriate components.

### Livewire Stack

After installation with `--stack=livewire`, you'll have:

-   **Volt Components**: Functional Livewire components in `resources/views/pages/teams/`

    -   `create.blade.php` - Team creation form
    -   `show.blade.php` - Team management interface
    -   `members.blade.php` - Member management
    -   `accept-invitation.blade.php` - Invitation acceptance
    -   `index.blade.php` - Teams listing

-   **Supporting Views**:

    -   `components/teams/layout.blade.php` - Team layout component
    -   `partials/teams-heading.blade.php` - Team navigation partial
    -   `emails/team-invitation.blade.php` - Email template

-   **Routes**: Team routes added to `routes/teams.php`

### Inertia.js Stack

Support for Inertia.js (React/Vue) components is planned for future releases.

## Events

The package dispatches several events that you can listen to:

**Team Events**:

-   `AddingTeam`: Before creating a team
-   `TeamCreated`: After a team is created
-   `TeamUpdated`: After a team is updated
-   `TeamDeleted`: After a team is deleted

**Member Events**:

-   `AddingTeamMember`: Before adding a member
-   `TeamMemberAdded`: After a member is added
-   `RemovingTeamMember`: Before removing a member
-   `TeamMemberRemoved`: After a member is removed
-   `TeamMemberUpdated`: After a member's role is updated

**Invitation Events**:

-   `InvitingTeamMember`: When sending an invitation

## Authorization

The package includes authorization policies and gates:

-   **TeamPolicy**: Controls team-level operations (view, update, delete, addTeamMember, etc.)
-   **Gates**: Automatic policy registration for team operations
-   **Middleware**: Built-in authorization checks in all actions

## Customization

### Override Default Actions

You can override any action in your `TeamsServiceProvider`:

```php
use Malico\Teams\Teams;

public function boot(): void
{
    Teams::createTeamsUsing(CustomCreateTeam::class);
    Teams::inviteTeamMembersUsing(CustomInviteTeamMember::class);
    // ... other actions
}
```

### Customize Models

You can specify custom models:

```php
use Malico\Teams\Teams;

Teams::useUserModel(App\Models\CustomUser::class);
Teams::useTeamModel(App\Models\CustomTeam::class);
Teams::useTeamInvitationModel(App\Models\CustomTeamInvitation::class);
Teams::useMembershipModel(App\Models\CustomMembership::class);
```

### Configure Invitation Duration

```php
Teams::invitationDurationDays(14); // Default is 7 days
```

## Available Contracts

The package provides these contracts for dependency injection:

-   `CreatesTeams`
-   `UpdatesTeamNames`
-   `DeletesTeams`
-   `ValidatesTeamDeletion`
-   `AddsTeamMembers`
-   `RemovesTeamMembers`
-   `InvitesTeamMembers`
-   `AcceptsTeamInvitations`
-   `DeclinesTeamInvitations`
-   `UpdatesTeamMemberRoles`

## Testing

Run the package tests:

```bash
phpunit
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Write tests for your changes
4. Ensure all tests pass
5. Submit a pull request

## Credits

This package is a fork of [Laravel Jetstream](https://github.com/laravel/jetstream) teams functionality, extracted into a standalone package for use in any Laravel application.

Special thanks to the Laravel team and contributors for the original implementation.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
