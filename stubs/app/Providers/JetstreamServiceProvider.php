<?php

namespace App\Providers;

use App\Actions\Teams\DeleteUser;
use Illuminate\Support\Facades\Vite;
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

        Teams::deleteUsersUsing(DeleteUser::class);

        Vite::prefetch(concurrency: 3);
    }

    /**
     * Configure the permissions that are available within the application.
     */
    protected function configurePermissions(): void
    {
        Teams::defaultApiTokenPermissions(['read']);

        Teams::permissions([
            'create',
            'read',
            'update',
            'delete',
        ]);
    }
}
