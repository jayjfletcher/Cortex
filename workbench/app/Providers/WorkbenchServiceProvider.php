<?php

namespace Workbench\App\Providers;

use Dedoc\Scramble\Scramble;
use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        config()->set('scramble.info.version', '0.1.0');
        config()->set('scramble.export_path', 'sdk/openapi.json');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Document the management API only — the dashboard shell route also
        // lives under the cortex prefix but is not part of the API.
        Scramble::routes(function (Route $route): bool {
            return Str::startsWith($route->uri(), 'cortex/')
                && ! Str::startsWith($route->uri(), 'cortex/ui');
        });
    }
}
