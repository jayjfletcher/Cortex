<?php

declare(strict_types=1);

use JayI\Cortex\Cortex;

it('resolves the singleton', function () {
    expect(app(Cortex::class))->toBeInstanceOf(Cortex::class);
});

it('returns the same instance from the container', function () {
    expect(app(Cortex::class))->toBe(app(Cortex::class));
});

it('merges the package config', function () {
    expect(config('cortex.routes.prefix'))->toBe('cortex')
        ->and(config('cortex.routes.middleware'))->toBe(['api'])
        ->and(config('cortex.ui.enabled'))->toBeTrue()
        ->and(config('cortex.ui.path'))->toBe('cortex/ui')
        ->and(config('cortex.ui.middleware'))->toBe(['web'])
        ->and(config('cortex.ui.auth.mode'))->toBe('session')
        ->and(config('cortex.ui.auth.token_resolver'))->toBeNull()
        ->and(config('cortex.mcp.web.enabled'))->toBeFalse()
        ->and(config('cortex.mcp.local.enabled'))->toBeFalse()
        ->and(config('cortex.tools'))->toBe([]);
});

it('loads the package translations', function () {
    expect(trans('cortex::messages.placeholder'))->toBe('Cortex placeholder translation.');
});

it('loads the package views', function () {
    expect(view()->exists('cortex::app'))->toBeTrue();
});

it('registers the artisan command', function () {
    $this->artisan('cortex:placeholder')
        ->expectsOutputToContain('Cortex placeholder command executed.')
        ->assertSuccessful();
});
