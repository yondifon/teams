<?php

namespace Malico\Teams\Tests;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase;

abstract class OrchestraTestCase extends TestCase
{
    use LazilyRefreshDatabase, WithWorkbench;

    protected function defineEnvironment($app)
    {
        $app['config']->set('database.default', 'testing');
    }
}
