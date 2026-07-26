<?php

declare(strict_types=1);

use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Models\PromptVersion;

it('lists versions newest first', function () {
    $prompt = Prompt::factory()->create(['slug' => 'support']);
    PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1]);
    PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 2]);

    $this->getJson(route('cortex.prompts.versions.index', 'support'))
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.version', 2);
});

it('creates a new version without publishing by default', function () {
    $prompt = Prompt::factory()->create(['slug' => 'support']);
    PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1]);

    $this->postJson(route('cortex.prompts.versions.store', 'support'), ['content' => 'v2'])
        ->assertCreated()
        ->assertJsonPath('data.version', 2)
        ->assertJsonPath('data.content', 'v2');

    expect($prompt->refresh()->published_version_id)->toBeNull();
});

it('creates and publishes a version when requested', function () {
    $prompt = Prompt::factory()->create(['slug' => 'support']);

    $this->postJson(route('cortex.prompts.versions.store', 'support'), [
        'content' => 'v1',
        'publish' => true,
    ])->assertCreated();

    expect($prompt->refresh()->publishedVersion?->content)->toBe('v1');
});

it('validates version creation', function () {
    Prompt::factory()->create(['slug' => 'support']);

    $this->postJson(route('cortex.prompts.versions.store', 'support'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['content']);
});

it('shows a version by number', function () {
    $prompt = Prompt::factory()->create(['slug' => 'support']);
    PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 3, 'content' => 'v3']);

    $this->getJson(route('cortex.prompts.versions.show', ['support', 3]))
        ->assertOk()
        ->assertJsonPath('data.content', 'v3');
});

it('returns 404 for unknown versions', function () {
    Prompt::factory()->create(['slug' => 'support']);

    $this->getJson(route('cortex.prompts.versions.show', ['support', 9]))->assertNotFound();
});

it('publishes a version', function () {
    $prompt = Prompt::factory()->create(['slug' => 'support']);
    PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1, 'content' => 'v1']);

    $this->postJson(route('cortex.prompts.versions.publish', ['support', 1]))
        ->assertOk()
        ->assertJsonPath('data.published_version.version', 1);
});
