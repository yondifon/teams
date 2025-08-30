<?php

namespace Malico\Teams\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\info;

#[AsCommand(name: 'teams:install')]
class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teams:install {--stack : The development stack that should be installed (inertia,livewire)}
                                              {--pest : Indicates if Pest should be installed}
                                              {--override : Override existing auth files like register.blade.php with team invitation support}
                                              {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Teams components and resources';

    public function handle(): int
    {
        $this->installBackendComponents();

        $this->installStackComponents();

        $this->publishEmailTemplates();

        return self::SUCCESS;
    }

    protected function installStackComponents()
    {
        $stack = $this->option('stack') ?: $this->detectFrontendStack();

        //  TODO: use vendor:publish for routes.
        $routeStub = $stack === 'livewire' ? 'livewire-teams.php' : 'inertia-teams.php';
        copy($this->stubsPath('routes/'.$routeStub), base_path('routes/teams.php'));
        $this->includeTeamsRoutesInWebPhp();

        if ($stack === 'livewire') {
            $this->installLivewireComponents();
        }

        if ($stack === 'inertia') {
            $this->installInertiaComponents();
        }
    }

    protected function installInertiaComponents()
    {
        $framework = $this->detectInertiaFramework();

        info("Detected Inertia framework: {$framework}");

        (new Filesystem)->ensureDirectoryExists(resource_path('js/pages/teams'));

        $reactStubsPath = $this->stubsPath('inertia/react/resources/js/pages/teams');
        $vueStubsPath = $this->stubsPath('inertia/vue/resources/js/pages/teams');

        return $framework === 'react' && is_dir($reactStubsPath)
            ? (new Filesystem)->copyDirectory($reactStubsPath, resource_path('js/pages/teams'))
            : (new Filesystem)->copyDirectory($vueStubsPath, resource_path('js/pages/teams'));
    }

    protected function publishEmailTemplates()
    {
        (new Filesystem)->ensureDirectoryExists(resource_path('views/emails'));
        (new Filesystem)->copyDirectory($this->stubsPath('resources/views/emails'), resource_path('views/emails'));
    }

    /**
     * Install backend components (models, actions, policies, etc.)
     *
     * @return void
     */
    protected function installBackendComponents()
    {
        // Service Providers...
        copy($this->stubsPath('app/Providers/TeamsServiceProvider.php'), app_path('Providers/TeamsServiceProvider.php'));
        ServiceProvider::addProviderToBootstrapFile('App\Providers\TeamsServiceProvider');

        $this->callSilent('vendor:publish', ['--tag' => 'teams-migrations', '--force' => true]);

        // Models...
        copy($this->stubsPath('app/Models/Membership.php'), app_path('Models/Membership.php'));
        copy($this->stubsPath('app/Models/Team.php'), app_path('Models/Team.php'));
        copy($this->stubsPath('app/Models/TeamInvitation.php'), app_path('Models/TeamInvitation.php'));
        copy($this->stubsPath('app/Models/User.php'), app_path('Models/User.php'));

        // Factories...
        copy(__DIR__.'/../../database/factories/UserFactory.php', base_path('database/factories/UserFactory.php'));
        copy(__DIR__.'/../../database/factories/TeamFactory.php', base_path('database/factories/TeamFactory.php'));

        // Actions are now provided by the package as defaults
        // Users can override them in their TeamsServiceProvider if needed

        // Policies...
        (new Filesystem)->ensureDirectoryExists(app_path('Policies'));
        (new Filesystem)->copy($this->stubsPath('app/Policies/TeamPolicy.php'), app_path('Policies/TeamPolicy.php'));

        // Listeners...
        (new Filesystem)->ensureDirectoryExists(app_path('Listeners'));
        (new Filesystem)->copy($this->stubsPath('app/Listeners/CreatePersonalTeam.php'), app_path('Listeners/CreatePersonalTeam.php'));
    }

    /**
     * Install Livewire components
     *
     * @return void
     */
    protected function installLivewireComponents()
    {
        $this->hasComposerPackage('livewire/livewire') ?: $this->requireComposerPackages('livewire/livewire:^3.0');
        $this->hasComposerPackage('livewire/volt') ?: $this->requireComposerPackages('livewire/volt:^1.0');

        info('Installing Volt functional components for teams');

        // Volt functional components go in resources/views/pages/
        (new Filesystem)->ensureDirectoryExists(resource_path('views/livewire'));
        (new Filesystem)->copyDirectory($this->stubsPath('livewire/resources/views/livewire/teams'), resource_path('views/livewire/teams'));

        // Override auth files if requested
        if ($this->option('override')) {
            $this->publishAuthOverrides();
        }

        // Supporting components and partials
        (new Filesystem)->ensureDirectoryExists(resource_path('views/components/teams'));
        (new Filesystem)->copyDirectory($this->stubsPath('livewire/resources/views/components/teams'), resource_path('views/components/teams'));

        (new Filesystem)->ensureDirectoryExists(resource_path('views/partials'));
        (new Filesystem)->copy($this->stubsPath('livewire/resources/views/partials/teams-heading.blade.php'), resource_path('views/partials/teams-heading.blade.php'));
    }

    /**
     * Publish auth file overrides with team invitation support.
     */
    protected function publishAuthOverrides()
    {
        info('Publishing auth file overrides with team invitation support...');

        // Ensure auth directory exists
        (new Filesystem)->ensureDirectoryExists(resource_path('views/livewire/auth'));

        // Copy register override
        copy(
            $this->stubsPath('livewire/resources/views/livewire/auth/register.blade.php'),
            resource_path('views/livewire/auth/register.blade.php')
        );

        // Copy login override
        copy(
            $this->stubsPath('livewire/resources/views/livewire/auth/login.blade.php'),
            resource_path('views/livewire/auth/login.blade.php')
        );

        $this->components->info('Auth overrides published with team invitation support.');
    }

    /**
     * Include the teams routes in web.php
     *
     * @return void
     */
    protected function includeTeamsRoutesInWebPhp()
    {
        return ! file_exists(base_path('routes/web.php')) ? $this->createWebPhpWithTeams() :
               ($this->teamsAlreadyIncluded() ? null : $this->addTeamsRequireToWebPhp());
    }

    protected function teamsAlreadyIncluded()
    {
        $webRoutes = file_get_contents(base_path('routes/web.php'));

        return str_contains($webRoutes, "require __DIR__.'/teams.php'") ||
               str_contains($webRoutes, 'require_once __DIR__."/teams.php"') ||
               str_contains($webRoutes, "include __DIR__.'/teams.php'");
    }

    protected function createWebPhpWithTeams()
    {
        file_put_contents(base_path('routes/web.php'), "<?php\n\nrequire __DIR__.'/teams.php';\n");
    }

    protected function addTeamsRequireToWebPhp()
    {

        file_put_contents(
            base_path('routes/web.php'),
            PHP_EOL.PHP_EOL."require __DIR__.'/teams.php';".PHP_EOL,
            FILE_APPEND
        );
    }

    /**
     * Get the path to the stubs directory.
     */
    protected function stubsPath(string $path = ''): string
    {
        return __DIR__.'/../../stubs'.($path ? '/'.$path : '');
    }

    /**
     * Determine if the given Composer package is installed.
     *
     * @param  string  $package
     * @return bool
     */
    protected function hasComposerPackage($package)
    {
        $packages = json_decode(file_get_contents(base_path('composer.json')), true);

        return array_key_exists($package, $packages['require'] ?? [])
            || array_key_exists($package, $packages['require-dev'] ?? []);
    }

    /**
     * Installs the given Composer Packages into the application.
     *
     * @param  mixed  $packages
     * @return bool
     */
    protected function requireComposerPackages($packages)
    {
        $composer = $this->option('composer');

        if ($composer !== 'global') {
            $command = [$this->phpBinary(), $composer, 'require'];
        }

        $command = array_merge(
            $command ?? ['composer', 'require'],
            is_array($packages) ? $packages : func_get_args()
        );

        return ! (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });
    }

    /**
     * Removes the given Composer Packages as "dev" dependencies.
     *
     * @param  mixed  $packages
     * @return bool
     */
    protected function removeComposerDevPackages($packages)
    {
        $composer = $this->option('composer');

        if ($composer !== 'global') {
            $command = [$this->phpBinary(), $composer, 'remove', '--dev'];
        }

        $command = array_merge(
            $command ?? ['composer', 'remove', '--dev'],
            is_array($packages) ? $packages : func_get_args()
        );

        return (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            }) === 0;
    }

    /**
     * Install the given Composer Packages as "dev" dependencies.
     *
     * @param  mixed  $packages
     * @return bool
     */
    protected function requireComposerDevPackages($packages)
    {
        $composer = $this->option('composer');

        if ($composer !== 'global') {
            $command = [$this->phpBinary(), $composer, 'require', '--dev'];
        }

        $command = array_merge(
            $command ?? ['composer', 'require', '--dev'],
            is_array($packages) ? $packages : func_get_args()
        );

        return (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            }) === 0;
    }

    /**
     * Replace a given string within a given file.
     *
     * @param  string  $replace
     * @param  string|array  $search
     * @param  string  $path
     * @return void
     */
    protected function replaceInFile($search, $replace, $path)
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }

    /**
     * Get the path to the appropriate PHP binary.
     *
     * @return string
     */
    protected function phpBinary()
    {
        return function_exists('Illuminate\Support\php_binary')
            ? \Illuminate\Support\php_binary()
            : ((new PhpExecutableFinder)->find(false) ?: 'php');
    }

    /**
     * Determine whether the project is already using Pest.
     *
     * @return bool
     */
    protected function isUsingPest()
    {
        return class_exists(\Pest\TestSuite::class);
    }

    protected function detectFrontendStack()
    {
        return $this->hasComposerPackage('livewire/livewire') ? 'livewire' :
               ($this->hasComposerPackage('inertiajs/inertia-laravel') ? 'inertia' : 'livewire');
    }

    protected function detectInertiaFramework()
    {
        return ! file_exists(base_path('package.json')) ? 'vue' : $this->detectFrameworkFromPackageJson();
    }

    protected function detectFrameworkFromPackageJson()
    {
        $packageJson = json_decode(file_get_contents(base_path('package.json')), true);
        $dependencies = array_merge(
            $packageJson['dependencies'] ?? [],
            $packageJson['devDependencies'] ?? []
        );

        return isset($dependencies['react']) || isset($dependencies['@inertiajs/react']) ? 'react' : 'vue';
    }
}
