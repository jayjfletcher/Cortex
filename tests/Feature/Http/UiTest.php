<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use JayI\Cortex\Tests\Fixtures\FixedTokenResolver;
use JayI\Cortex\Tests\Fixtures\InvalidTokenResolver;

it('serves the dashboard shell', function () {
    $response = $this->get('/cortex/ui');

    $response->assertOk()
        ->assertViewIs('cortex::app')
        ->assertSee('id="cortex"', false)
        ->assertSee('window.CortexConfig', false);
});

it('injects the dashboard config', function () {
    $response = $this->get('/cortex/ui');

    $response->assertOk()->assertViewHas('cortexConfig', function (array $config): bool {
        return str_ends_with($config['apiBase'], '/cortex')
            && $config['basePath'] === '/cortex/ui'
            && $config['auth']['mode'] === 'session'
            && $config['auth']['token'] === null
            && is_string($config['csrfToken']);
    });
});

it('serves the shell for nested dashboard paths', function () {
    $this->get('/cortex/ui/prompts/example/edit')
        ->assertOk()
        ->assertViewIs('cortex::app');
});

it('only responds to get requests', function () {
    $this->post('/cortex/ui/prompts')->assertMethodNotAllowed();
});

it('applies the configured middleware', function () {
    $route = Route::getRoutes()->getByName('cortex.ui');

    expect($route)->not->toBeNull()
        ->and($route->gatherMiddleware())->toContain('web');
});

it('injects the resolved token in token mode', function () {
    config()->set('cortex.ui.auth.mode', 'token');
    config()->set('cortex.ui.auth.token_resolver', FixedTokenResolver::class);

    $this->get('/cortex/ui')->assertOk()->assertViewHas('cortexConfig', function (array $config): bool {
        return $config['auth']['mode'] === 'token'
            && $config['auth']['token'] === 'fixed-token';
    });
});

it('injects a null token when no resolver is configured', function () {
    config()->set('cortex.ui.auth.mode', 'token');

    $this->get('/cortex/ui')->assertOk()->assertViewHas('cortexConfig', function (array $config): bool {
        return $config['auth']['mode'] === 'token'
            && $config['auth']['token'] === null;
    });
});

it('publishes the compiled dashboard assets', function () {
    File::deleteDirectory(public_path('vendor/cortex'));

    $this->artisan('vendor:publish', ['--tag' => 'cortex-assets'])->assertSuccessful();

    expect(File::exists(public_path('vendor/cortex/app.js')))->toBeTrue()
        ->and(File::exists(public_path('vendor/cortex/app.css')))->toBeTrue();

    File::deleteDirectory(public_path('vendor/cortex'));
});

it('rejects resolvers that do not implement the contract', function () {
    config()->set('cortex.ui.auth.mode', 'token');
    config()->set('cortex.ui.auth.token_resolver', InvalidTokenResolver::class);

    $this->withoutExceptionHandling()->get('/cortex/ui');
})->throws(RuntimeException::class);
