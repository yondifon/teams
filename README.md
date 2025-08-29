# Laravel Teams

A comprehensive team management package for Laravel applications that provides robust multi-tenant functionality with support for team creation, member management, invitations, and role-based permissions.

## Features

- **Team Management**: Complete team creation, updating, and deletion functionality
- **Member Management**: Add, remove, and manage team members with role-based access control
- **Team Invitations**: Email-based invitation system with acceptance and decline workflows
- **Role & Permission System**: Flexible role definitions with granular permission control
- **Multi-Stack Support**: Compatible with both Livewire and Inertia.js implementations
- **Event-Driven Architecture**: Comprehensive event system for custom business logic integration
- **Personal Teams**: Automatic personal team creation for new users
- **Authorization Policies**: Built-in policy classes for secure team operations
- **Testing Support**: Full Pest testing framework integration with comprehensive test coverage

## Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher

## Installation

Install the package via Composer:

```bash
composer require malico/teams
```

Run the installation command to publish migrations, models, and configuration files:

```bash
php artisan teams:install
```

The installation process will:

- Publish and run database migrations
- Publish configuration files
- Create necessary stub files for your chosen stack (Livewire or Inertia.js)
- Set up authentication overrides with team invitation support

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

Define team roles in your application's service provider:

```php
use Malico\Teams\Teams;

public function boot(): void
{
    Teams::role('owner', 'Owner', [
        'team:read',
        'team:update',
        'team:delete',
        'team:invite-members',
        'team:remove-members',
    ])->description('Team owner with full administrative access');

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

## Usage

### Creating Teams

```php
use App\Actions\Teams\CreateTeam;

$team = app(CreateTeam::class)->create($user, [
    'name' => 'Development Team',
    'description' => 'Main development team for the project',
]);
```

### Managing Team Members

```php
use App\Actions\Teams\InviteTeamMember;
use App\Actions\Teams\AcceptTeamInvitation;
use App\Actions\Teams\DeclineTeamInvitation;

// Invite a team member
app(InviteTeamMember::class)->invite($user, $team, 'developer@example.com', 'admin');

// Accept an invitation
app(AcceptTeamInvitation::class)->accept($user, $teamInvitation);

// Decline an invitation
app(DeclineTeamInvitation::class)->decline($teamInvitation);
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

// Get all user's teams
$teams = $user->allTeams();

// Get teams where user owns
$ownedTeams = $user->ownedTeams;

// Get teams where user is a member
$memberTeams = $user->teams;
```

## Frontend Integration

This package supports both Livewire and Inertia.js stacks. The installation command will scaffold the appropriate components based on your selection.

### Livewire Stack

After installation, you'll have Livewire components for:

- Team creation and management
- Member invitation and management
- Team switching interface
- Invitation acceptance/decline pages

### Inertia.js Stack

After installation, you'll have Vue.js components and controllers for:

- Team management interfaces
- Member management
- Invitation handling
- Team switching functionality

## Events

The package dispatches several events that you can listen to:

- `TeamCreated`: Fired when a team is created
- `TeamUpdated`: Fired when a team is updated
- `TeamDeleted`: Fired when a team is deleted
- `TeamMemberAdded`: Fired when a member is added to a team
- `TeamMemberRemoved`: Fired when a member is removed from a team
- `TeamInvitationSent`: Fired when an invitation is sent
- `TeamInvitationAccepted`: Fired when an invitation is accepted
- `TeamInvitationDeclined`: Fired when an invitation is declined

## Testing

The package includes comprehensive test coverage using the Pest testing framework. You can run the tests using:

```bash
composer test
```

## Authorization

The package includes authorization policies for secure team operations:

- `TeamPolicy`: Controls team-level operations
- `TeamMemberPolicy`: Controls member management operations
- `TeamInvitationPolicy`: Controls invitation operations

## Database Schema

The package creates the following database tables:

- `teams`: Stores team information
- `team_members`: Pivot table for team memberships
- `team_invitations`: Stores pending team invitations

## Contributing

Contributions are welcome. Please ensure that your code follows Laravel conventions and includes appropriate test coverage.

1. Fork the repository
2. Create a feature branch
3. Write tests for your changes
4. Ensure all tests pass
5. Submit a pull request

## Security

If you discover any security-related issues, please email the maintainer instead of using the issue tracker.

## Credits

This package was extracted and enhanced from Laravel Jetstream's team functionality to provide a standalone, framework-agnostic solution for team management in Laravel applications.
