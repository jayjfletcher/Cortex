<?php

declare(strict_types=1);

it('lists text-capable providers with their models', function () {
    $response = $this->getJson(route('cortex.providers.index'))->assertOk();

    $providers = collect($response->json('data'));

    expect($providers)->not->toBeEmpty();

    $anthropic = $providers->firstWhere('name', 'anthropic');

    expect($anthropic)->not->toBeNull()
        ->and($anthropic['models'])->toContain('claude-sonnet-5')
        ->and($anthropic['default_model'])->toBe('claude-sonnet-5');
});

it('honors a configured provider allowlist', function () {
    config()->set('cortex.providers', [
        'anthropic' => ['claude-sonnet-5', 'claude-opus-4-8'],
    ]);

    $this->getJson(route('cortex.providers.index'))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'anthropic')
        ->assertJsonPath('data.0.models', ['claude-sonnet-5', 'claude-opus-4-8'])
        ->assertJsonPath('data.0.default_model', 'claude-sonnet-5');
});
