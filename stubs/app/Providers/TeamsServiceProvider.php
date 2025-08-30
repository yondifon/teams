<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Malico\Teams\Teams;

class TeamsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePermissions();

        // Teams::createTeamsUsing(CreateTeam::class);
    }

    /**
     * Configure the roles and permissions that are available within the application.
     */
    protected function configurePermissions(): void
    {
        Teams::role('admin', 'Administrator', [
            'team:create',
            'team:read',
            'team:update',
            'team:members:*',
            // 'api-keys:*',
        ])->description('Administrator users can perform any action.');

        Teams::role('member', 'Member', [
            'team:read',
            'team:members:read',
        ])->description('Member users can read teams and their members.');
    }
}
