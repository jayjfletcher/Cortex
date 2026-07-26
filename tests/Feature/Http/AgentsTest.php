<?php

declare(strict_types=1);

use JayI\Cortex\Models\Agent;
use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Models\PromptVersion;
use JayI\Cortex\Tests\Fixtures\EchoTool;
use JayI\Cortex\Tools\ToolRegistry;

it('lists agents', function () {
    Agent::factory()->create(['slug' => 'helper']);

    $this->getJson(route('cortex.agents.index'))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', 'helper');
});

it('creates an agent with tools, prompt, and sub-agents', function () {
    app(ToolRegistry::class)->register('echo', EchoTool::class);
    $prompt = Prompt::factory()->create(['slug' => 'support']);
    PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1]);
    Agent::factory()->create(['slug' => 'researcher']);

    $this->postJson(route('cortex.agents.store'), [
        'name' => 'Coordinator',
        'slug' => 'coordinator',
        'provider' => 'anthropic',
        'model' => 'claude-sonnet-5',
        'settings' => ['temperature' => 0.3],
        'tools' => ['echo'],
        'prompt' => 'support',
        'prompt_version' => 1,
        'sub_agents' => ['researcher'],
    ])
        ->assertCreated()
        ->assertJsonPath('data.slug', 'coordinator')
        ->assertJsonPath('data.tools', ['echo'])
        ->assertJsonPath('data.prompt', 'support')
        ->assertJsonPath('data.prompt_version', 1)
        ->assertJsonPath('data.sub_agents', ['researcher']);
});

it('rejects unregistered tools', function () {
    $this->postJson(route('cortex.agents.store'), [
        'name' => 'Broken',
        'slug' => 'broken',
        'tools' => ['missing'],
    ])->assertJsonValidationErrors(['tools.0']);
});

it('rejects unknown prompt slugs', function () {
    $this->postJson(route('cortex.agents.store'), [
        'name' => 'Broken',
        'slug' => 'broken',
        'prompt' => 'missing',
    ])->assertJsonValidationErrors(['prompt']);
});

it('shows an agent', function () {
    Agent::factory()->create(['slug' => 'helper']);

    $this->getJson(route('cortex.agents.show', 'helper'))
        ->assertOk()
        ->assertJsonPath('data.slug', 'helper')
        ->assertJsonPath('data.sub_agents', []);
});

it('returns 404 for unknown agents', function () {
    $this->getJson(route('cortex.agents.show', 'missing'))->assertNotFound();
});

it('updates an agent with sync semantics', function () {
    app(ToolRegistry::class)->register('echo', EchoTool::class);
    $agent = Agent::factory()->create(['slug' => 'helper', 'tools' => ['old']]);
    $agent->subAgents()->attach(Agent::factory()->create());

    $this->patchJson(route('cortex.agents.update', 'helper'), [
        'tools' => ['echo'],
        'sub_agents' => [],
    ])
        ->assertOk()
        ->assertJsonPath('data.tools', ['echo'])
        ->assertJsonPath('data.sub_agents', []);
});

it('rejects circular sub-agent updates', function () {
    $a = Agent::factory()->create(['slug' => 'agent-a']);
    $b = Agent::factory()->create(['slug' => 'agent-b']);
    $a->subAgents()->attach($b);

    $this->patchJson(route('cortex.agents.update', 'agent-b'), [
        'sub_agents' => ['agent-a'],
    ])->assertJsonValidationErrors(['sub_agents']);
});

it('deletes an agent', function () {
    Agent::factory()->create(['slug' => 'helper']);

    $this->deleteJson(route('cortex.agents.destroy', 'helper'))->assertNoContent();

    expect(Agent::query()->count())->toBe(0);
});
