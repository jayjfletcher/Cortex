<?php

declare(strict_types=1);

namespace JayI\Cortex\Tests\Feature\Http;

use Illuminate\Support\Facades\Route;
use JayI\Cortex\Tests\TestCase;

final class UiConfiguredTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('cortex.ui.path', 'admin/cortex');
        $app['config']->set('cortex.ui.middleware', ['web', 'auth']);
    }

    public function test_it_registers_the_dashboard_at_the_configured_path_with_the_configured_middleware(): void
    {
        $route = Route::getRoutes()->getByName('cortex.ui');

        $this->assertNotNull($route);
        $this->assertSame('admin/cortex/{view?}', $route->uri());
        $this->assertContains('web', $route->gatherMiddleware());
        $this->assertContains('auth', $route->gatherMiddleware());
    }
}
