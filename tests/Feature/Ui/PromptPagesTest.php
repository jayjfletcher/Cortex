<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use JayI\Cortex\Models\Agent;
use JayI\Cortex\Models\Prompt;

beforeEach(function (): void {
    ValidateCsrfToken::except(['*']);

    // Atrium denies access outside local until a gate is defined.
    app()->detectEnvironment(fn (): string => 'local');
});

it('lists prompts', function (): void {
    Prompt::query()->create(['name' => 'Greeting', 'slug' => 'greeting']);

    $this->get(route('atrium.cortex.prompts.index'))
        ->assertOk()
        ->assertSee('Greeting')
        ->assertSee('greeting');
});

it('shows an empty state with no prompts', function (): void {
    $this->get(route('atrium.cortex.prompts.index'))
        ->assertOk()
        ->assertSee(__('cortex::cortex.no_prompts'));
});

it('creates a prompt through the form', function (): void {
    $this->post(route('atrium.cortex.prompts.store'), [
        'name' => 'Summarise',
        'slug' => 'summarise',
        'description' => 'Summarises text.',
        'content' => 'Summarise the following: {{ input }}',
        'publish' => true,
    ])->assertRedirect(route('atrium.cortex.prompts.show', 'summarise'));

    $prompt = Prompt::query()->where('slug', 'summarise')->firstOrFail();

    expect($prompt->name)->toBe('Summarise')
        ->and($prompt->publishedVersion)->not->toBeNull();
});

it('rejects a prompt with no name', function (): void {
    $this->post(route('atrium.cortex.prompts.store'), ['slug' => 'nameless'])
        ->assertSessionHasErrors('name');
});

it('shows a prompt with its versions', function (): void {
    $prompt = Prompt::query()->create(['name' => 'Greeting', 'slug' => 'greeting']);
    $prompt->versions()->create(['version' => 1, 'content' => 'Say hello']);

    $this->get(route('atrium.cortex.prompts.show', 'greeting'))
        ->assertOk()
        ->assertSee('Greeting')
        ->assertSee('v1');
});

it('adds a version from the prompt page', function (): void {
    $prompt = Prompt::query()->create(['name' => 'Greeting', 'slug' => 'greeting']);

    $this->post(route('atrium.cortex.prompts.versions.store', 'greeting'), [
        'content' => 'Say hello warmly',
        'publish' => true,
    ])->assertRedirect();

    expect($prompt->fresh()->publishedVersion?->content)->toBe('Say hello warmly');
});

it('publishes an existing version', function (): void {
    $prompt = Prompt::query()->create(['name' => 'Greeting', 'slug' => 'greeting']);
    $prompt->versions()->create(['version' => 1, 'content' => 'First']);
    $prompt->versions()->create(['version' => 2, 'content' => 'Second']);

    $this->post(route('atrium.cortex.prompts.versions.publish', ['greeting', 2]))
        ->assertRedirect();

    expect($prompt->fresh()->publishedVersion?->version)->toBe(2);
});

it('deletes a prompt', function (): void {
    Prompt::query()->create(['name' => 'Greeting', 'slug' => 'greeting']);

    $this->delete(route('atrium.cortex.prompts.destroy', 'greeting'))
        ->assertRedirect(route('atrium.cortex.prompts.index'));

    expect(Prompt::query()->count())->toBe(0);
});

it('refuses to delete a prompt an agent still uses', function (): void {
    $prompt = Prompt::query()->create(['name' => 'Greeting', 'slug' => 'greeting']);

    Agent::query()->create([
        'name' => 'Helper',
        'slug' => 'helper',
        'prompt_id' => $prompt->getKey(),
    ]);

    $this->delete(route('atrium.cortex.prompts.destroy', 'greeting'))
        ->assertSessionHasErrors('prompt');

    expect(Prompt::query()->count())->toBe(1);
});

it('renders the create and edit forms', function (): void {
    Prompt::query()->create(['name' => 'Greeting', 'slug' => 'greeting']);

    $this->get(route('atrium.cortex.prompts.create'))->assertOk()->assertSee(__('cortex::cortex.slug_hint'));
    $this->get(route('atrium.cortex.prompts.edit', 'greeting'))->assertOk()->assertSee(__('cortex::cortex.versioned_hint'));
});
