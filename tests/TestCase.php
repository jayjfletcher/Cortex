<?php

declare(strict_types=1);

namespace JayI\Cortex\Tests;

use JayI\Cortex\CortexServiceProvider;
use Laravel\Ai\AiServiceProvider;
use Laravel\Mcp\Server\McpServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            AiServiceProvider::class,
            McpServiceProvider::class,
            CortexServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing.foreign_key_constraints', true);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(dirname(__DIR__).'/database/migrations');
    }
}
