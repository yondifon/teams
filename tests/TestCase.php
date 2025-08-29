<?php

namespace Malico\Teams\Tests;

use App\Models\Team;
use App\Policies\TeamPolicy;
use Illuminate\Support\Facades\Gate;
use Orchestra\Testbench\Concerns\WithWorkbench;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use WithWorkbench;

    protected function defineEnvironment($app)
    {
        Gate::guessPolicyNamesUsing(function ($class) {
            $baseName = class_basename($class);

            return match ($class) {
                Team::class => TeamPolicy::class,
                default => "App\\Policies\\{$baseName}Policy",
            };
        });
    }
}
