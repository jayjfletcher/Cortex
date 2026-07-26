<?php

declare(strict_types=1);

use JayI\Cortex\Models\Agent;
use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Models\PromptVersion;

it('lists prompts', function () {
    $prompt = Prompt::factory()->create();
    $version = PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1]);
    $prompt->published_version_id = $version->getKey();
    $prompt->save();

    $this->getJson(route('cortex.prompts.index'))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', $prompt->slug)
        ->assertJsonPath('data.0.published_version.version', 1);
});

it('creates a prompt', function () {
    $this->postJson(route('cortex.prompts.store'), [
        'name' => 'Support',
        'slug' => 'support',
        'content' => 'You are helpful.',
    ])
        ->assertCreated()
        ->assertJsonPath('data.slug', 'support')
        ->assertJsonPath('data.published_version.version', 1)
        ->assertJsonPath('data.published_version.content', 'You are helpful.');
});

it('validates prompt creation', function () {
    $this->postJson(route('cortex.prompts.store'), ['name' => 'X'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['slug', 'content']);
});

it('rejects duplicate slugs', function () {
    Prompt::factory()->create(['slug' => 'support']);

    $this->postJson(route('cortex.prompts.store'), [
        'name' => 'Support',
        'slug' => 'support',
        'content' => 'Hi.',
    ])->assertJsonValidationErrors(['slug']);
});

it('shows a prompt by slug', function () {
    Prompt::factory()->create(['slug' => 'support']);

    $this->getJson(route('cortex.prompts.show', 'support'))
        ->assertOk()
        ->assertJsonPath('data.slug', 'support');
});

it('returns 404 for unknown prompts', function () {
    $this->getJson(route('cortex.prompts.show', 'missing'))->assertNotFound();
});

it('updates prompt metadata', function () {
    Prompt::factory()->create(['slug' => 'support', 'name' => 'Old']);

    $this->patchJson(route('cortex.prompts.update', 'support'), ['name' => 'New'])
        ->assertOk()
        ->assertJsonPath('data.name', 'New');
});

it('deletes a prompt', function () {
    Prompt::factory()->create(['slug' => 'support']);

    $this->deleteJson(route('cortex.prompts.destroy', 'support'))->assertNoContent();

    expect(Prompt::query()->count())->toBe(0);
});

it('refuses to delete a prompt attached to an agent', function () {
    $prompt = Prompt::factory()->create(['slug' => 'support']);
    Agent::factory()->create(['prompt_id' => $prompt->getKey()]);

    $this->deleteJson(route('cortex.prompts.destroy', 'support'))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['prompt']);
});
