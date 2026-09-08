<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use JayI\Cortex\Models\Agent;
use JayI\Cortex\Models\ToolDescription;
use JayI\Cortex\Tests\Fixtures\EchoTool;

beforeEach(function (): void {
    ValidateCsrfToken::except(['*']);

    app()->detectEnvironment(fn (): string => 'local');

    config()->set('cortex.tools', ['echo' => EchoTool::class]);
});

it('lists agents', function (): void {
    Agent::query()->create(['name' => 'Helper', 'slug' => 'helper', 'provider' => 'openai', 'model' => 'gpt-4']);

    $this->get(route('atrium.cortex.agents.index'))
        ->assertOk()
        ->assertSee('Helper')
        ->assertSee('openai');
});

it('creates an agent and drops blank settings', function (): void {
    $this->post(route('atrium.cortex.agents.store'), [
        'name' => 'Helper',
        'slug' => 'helper',
        'settings' => ['temperature' => '0.7', 'max_steps' => '', 'top_p' => ''],
    ])->assertRedirect(route('atrium.cortex.agents.index'));

    $agent = Agent::query()->where('slug', 'helper')->firstOrFail();

    // Blank inputs are omitted rather than stored as empty strings.
    expect($agent->settings)->toBe(['temperature' => 0.7]);
});

it('renders the agent form with a saved provider that is no longer offered', function (): void {
    Agent::query()->create(['name' => 'Helper', 'slug' => 'helper', 'provider' => 'retired-provider']);

    $this->get(route('atrium.cortex.agents.edit', 'helper'))
        ->assertOk()
        ->assertSee('retired-provider');
});

it('deletes an agent', function (): void {
    Agent::query()->create(['name' => 'Helper', 'slug' => 'helper']);

    $this->delete(route('atrium.cortex.agents.destroy', 'helper'))->assertRedirect();

    expect(Agent::query()->count())->toBe(0);
});

it('renders the run page and preselects an agent', function (): void {
    Agent::query()->create(['name' => 'Helper', 'slug' => 'helper']);

    $this->get(route('atrium.cortex.run', ['agent' => 'helper']))
        ->assertOk()
        ->assertSee('Helper');
});

it('lists tools', function (): void {
    $this->get(route('atrium.cortex.tools.index'))->assertOk()->assertSee('echo');
});

it('shows a tool description falling back to the code declaration', function (): void {
    // No override row exists, so the page shows what the class declares
    // rather than the 404 the JSON API would answer with.
    $this->get(route('atrium.cortex.tools.description', 'echo'))
        ->assertOk()
        ->assertSee(__('cortex::cortex.from_code'));
});

it('404s for a tool that is not registered', function (): void {
    $this->get(route('atrium.cortex.tools.description', 'nope'))->assertNotFound();
});

it('creates and publishes a description override', function (): void {
    $this->post(route('atrium.cortex.tools.description.store', 'echo'), [
        'content' => 'A better description.',
        'publish' => true,
    ])->assertRedirect();

    $description = ToolDescription::query()->where('tool', 'echo')->firstOrFail();

    expect($description->publishedVersion?->content)->toBe('A better description.');

    $this->get(route('atrium.cortex.tools.description', 'echo'))
        ->assertOk()
        ->assertSee('A better description.');
});

it('removes a description override', function (): void {
    $this->post(route('atrium.cortex.tools.description.store', 'echo'), ['content' => 'Override', 'publish' => true]);

    $this->delete(route('atrium.cortex.tools.description.destroy', 'echo'))->assertRedirect();

    expect(ToolDescription::query()->count())->toBe(0);
});

it('lists mcp servers', function (): void {
    $this->get(route('atrium.cortex.servers.index'))->assertOk()->assertSee('cortex');
});

it('shows server instructions', function (): void {
    $this->get(route('atrium.cortex.servers.instructions', 'cortex'))
        ->assertOk()
        ->assertSee(__('cortex::cortex.from_code'));
});
