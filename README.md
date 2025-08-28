# Malico Teams

A robust team management package for Laravel applications, extracted from the excellent Laravel Teams.

## Overview

This package provides comprehensive team functionality for Laravel applications, including:

-   **Team Creation & Management** - Create and manage teams with ease
-   **Team Member Management** - Add, remove, and manage team members
-   **Role-based Permissions** - Flexible role and permission system for teams
-   **Team Invitations** - Send email invitations to team members
-   **Seamless Integration** - Works with any Laravel application

## Key Features

-   ✅ **Clean Backend Logic** - Pure team functionality without UI dependencies
-   ✅ **Battle-Tested Code** - Based on production-ready Laravel Teams
-   ✅ **Framework Agnostic UI** - Implement any frontend (Blade, Vue, React, etc.)
-   ✅ **Event-Driven Architecture** - Rich event system for custom business logic
-   ✅ **Authorization Ready** - Built-in policy system
-   ✅ **Laravel Conventions** - Follows Laravel's excellent naming and patterns

## Installation

```bash
composer require malico/teams
```

Then run the installation command:

```bash
php artisan teams:install
```

This will publish the necessary migrations, models, and configuration files.

## Usage

Add the `HasTeams` trait to your User model:

```php
use Malico\Teams\HasTeams;

class User extends Authenticatable
{
    use HasTeams;

    // ...
}
```

Create teams programmatically:

```php
use Malico\Teams\Teams;

// Create a team
$team = Teams::createTeamsUsing(CreateTeam::class);

// Add team members
Teams::addTeamMembersUsing(AddTeamMember::class);

// Define roles
Teams::role('admin', 'Administrator', ['read', 'write', 'delete'])
    ->description('Has full access to the team');
```

## Laravel Community Invitation

**If the Laravel team would like to adopt this package as `laravel/teams`, that would be incredible!**

This package represents a clean extraction of Teams's excellent team functionality, making it available as a standalone solution. The codebase maintains all of Teams's battle-tested patterns while removing UI dependencies.

I believe this would be valuable as an official Laravel package, as team functionality is a common need across many Laravel applications. The package is ready for community adoption and would benefit from the Laravel team's stewardship.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).

---

_Originally extracted from Laravel Teams with ❤️_
