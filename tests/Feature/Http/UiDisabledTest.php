<?php

declare(strict_types=1);

namespace JayI\Cortex\Tests\Feature\Http;

use Illuminate\Support\Facades\Route;
use JayI\Cortex\Tests\TestCase;

final class UiDisabledTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('cortex.ui.enabled', false);
    }

    public function test_it_does_not_register_the_dashboard_route_when_disabled(): void
    {
        $this->assertFalse(Route::has('cortex.ui'));

        $this->get('/cortex/ui')->assertNotFound();
    }
}
