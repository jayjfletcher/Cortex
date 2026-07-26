<?php

declare(strict_types=1);

namespace JayI\Cortex;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use JayI\Cortex\Console\Commands\CortexCommand;
use JayI\Cortex\Http\Controllers\UiController;
use JayI\Cortex\Mcp\CortexServer;
use Laravel\Mcp\Facades\Mcp;

class CortexServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cortex.php', 'cortex');

        $this->app->singleton(Tools\ToolRegistry::class);

        $this->app->singleton(Cortex::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/cortex.php');

        $this->registerUiRoutes();

        $this->registerMcpServers();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'cortex');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'cortex');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/cortex.php' => config_path('cortex.php'),
        ], ['cortex', 'cortex-config']);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/cortex'),
        ], ['cortex', 'cortex-views']);

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/cortex'),
        ], ['cortex', 'cortex-lang']);

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/cortex'),
        ], ['cortex', 'cortex-assets']);

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], ['cortex', 'cortex-migrations']);

        $this->commands([
            CortexCommand::class,
        ]);
    }

    /**
     * Register the dashboard UI route when enabled in the config.
     */
    private function registerUiRoutes(): void
    {
        $config = $this->app->make('config');

        if ($config->get('cortex.ui.enabled') !== true) {
            return;
        }

        /** @var array<int, string> $middleware */
        $middleware = $config->get('cortex.ui.middleware', []);

        $path = trim((string) $config->get('cortex.ui.path', 'cortex/ui'), '/');

        Route::middleware($middleware)
            ->get($path.'/{view?}', UiController::class)
            ->where('view', '.*')
            ->name('cortex.ui');
    }

    /**
     * Register the Cortex MCP server transports enabled in the config.
     */
    private function registerMcpServers(): void
    {
        $config = $this->app->make('config');

        if ($config->get('cortex.mcp.web.enabled') === true) {
            /** @var array<int, string> $middleware */
            $middleware = $config->get('cortex.mcp.web.middleware', []);

            Mcp::web((string) $config->get('cortex.mcp.web.route'), CortexServer::class)
                ->middleware($middleware);
        }

        if ($config->get('cortex.mcp.local.enabled') === true) {
            Mcp::local((string) $config->get('cortex.mcp.local.handle'), CortexServer::class);
        }
    }
}
