<?php

namespace Malico\Teams;

use Illuminate\Support\ServiceProvider;

class TeamsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerActionBindings();
    }

    /**
     * Register the action bindings for the package.
     */
    protected function registerActionBindings(): void
    {
        $this->app->singleton(Contracts\CreatesTeams::class, Actions\CreateTeam::class);
        $this->app->singleton(Contracts\InvitesTeamMembers::class, Actions\InviteTeamMember::class);
        $this->app->singleton(Contracts\SendsTeamInvitations::class, Actions\SendTeamInvitation::class);
        $this->app->singleton(Contracts\AcceptsTeamInvitations::class, Actions\AcceptTeamInvitation::class);
        $this->app->singleton(Contracts\DeclinesTeamInvitations::class, Actions\DeclineTeamInvitation::class);
        $this->app->singleton(Contracts\AddsTeamMembers::class, Actions\AddTeamMember::class);
        $this->app->singleton(Contracts\RemovesTeamMembers::class, Actions\RemoveTeamMember::class);
        $this->app->singleton(Contracts\UpdatesTeamNames::class, Actions\UpdateTeamName::class);
        $this->app->singleton(Contracts\UpdatesTeamMemberRoles::class, Actions\UpdateTeamMemberRole::class);
        $this->app->singleton(Contracts\DeletesTeams::class, Actions\DeleteTeam::class);
        $this->app->singleton(Contracts\ValidatesTeamDeletion::class, Actions\ValidateTeamDeletion::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configurePublishing();

        $this->configureCommands();
    }

    /**
     * Configure publishing for the package.
     *
     * @return void
     */
    protected function configurePublishing()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishesMigrations([
            __DIR__.'/../database/migrations/2020_05_21_100000_create_teams_table.php' => database_path('migrations/2020_05_21_100000_create_teams_table.php'),
        ], 'teams-migrations');

        // TODO: publish routes
        // $this->publishes([
        //     __DIR__.'/../routes/'.config('jetstream.stack').'.php' => base_path('routes/jetstream.php'),
        // ], 'teams-routes');

    }

    /**
     * Configure the commands offered by the application.
     *
     * @return void
     */
    protected function configureCommands()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Console\InstallCommand::class,
        ]);
    }
}
