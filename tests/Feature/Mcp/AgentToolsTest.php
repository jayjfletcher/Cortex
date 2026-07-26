<?php

declare(strict_types=1);

use Illuminate\Testing\Fluent\AssertableJson;
use JayI\Cortex\Mcp\CortexServer;
use JayI\Cortex\Mcp\Tools\CreateAgentTool;
use JayI\Cortex\Mcp\Tools\DeleteAgentTool;
use JayI\Cortex\Mcp\Tools\ListAgentsTool;
use JayI\Cortex\Mcp\Tools\ListToolsTool;
use JayI\Cortex\Mcp\Tools\ShowAgentTool;
use JayI\Cortex\Mcp\Tools\UpdateAgentTool;
use JayI\Cortex\Models\Agent;
use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Models\PromptVersion;
use JayI\Cortex\Tests\Fixtures\EchoTool;
use JayI\Cortex\Tools\ToolRegistry;

it('creates an agent with parity to the http payload', function () {
    app(ToolRegistry::class)->register('echo', EchoTool::class);
    $prompt = Prompt::factory()->create(['slug' => 'support']);
    PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1]);
    Agent::factory()->create(['slug' => 'researcher']);

    $mcp = CortexServer::tool(CreateAgentTool::class, [
        'name' => 'Coordinator',
        'slug' => 'coordinator',
        'settings' => ['temperature' => 0.3],
        'tools' => ['echo'],
        'prompt' => 'support',
        'prompt_version' => 1,
        'sub_agents' => ['researcher'],
    ])->assertOk();

    $http = $this->getJson(route('cortex.agents.show', 'coordinator'))->json('data');

    $mcp->assertStructuredContent($http);

    expect($http['tools'])->toBe(['echo'])
        ->and($http['prompt'])->toBe('support')
        ->and($http['prompt_version'])->toBe(1)
        ->and($http['sub_agents'])->toBe(['researcher']);
});

it('validates create agent input against the registry', function () {
    CortexServer::tool(CreateAgentTool::class, [
        'name' => 'Broken',
        'slug' => 'broken',
        'tools' => ['missing'],
    ])->assertHasErrors();
});

it('lists agents in a data envelope', function () {
    Agent::factory()->create(['slug' => 'helper']);

    CortexServer::tool(ListAgentsTool::class)
        ->assertOk()
        ->assertStructuredContent(
            fn (AssertableJson $json) => $json
                ->count('data', 1)
                ->where('data.0.slug', 'helper')
                ->etc(),
        );
});

it('shows an agent by slug', function () {
    Agent::factory()->create(['slug' => 'helper']);

    CortexServer::tool(ShowAgentTool::class, ['slug' => 'helper'])
        ->assertOk()
        ->assertSee('helper');
});

it('errors not found for unknown agent slugs', function () {
    CortexServer::tool(ShowAgentTool::class, ['slug' => 'missing'])
        ->assertHasErrors(['Not found.']);
});

it('updates an agent with sync semantics', function () {
    app(ToolRegistry::class)->register('echo', EchoTool::class);
    $agent = Agent::factory()->create(['slug' => 'helper', 'tools' => ['old']]);
    $agent->subAgents()->attach(Agent::factory()->create());

    CortexServer::tool(UpdateAgentTool::class, [
        'slug' => 'helper',
        'tools' => ['echo'],
        'sub_agents' => [],
    ])->assertOk();

    expect($agent->refresh()->tools)->toBe(['echo'])
        ->and($agent->subAgents)->toHaveCount(0);
});

it('rejects circular sub-agent updates', function () {
    $a = Agent::factory()->create(['slug' => 'agent-a']);
    $b = Agent::factory()->create(['slug' => 'agent-b']);
    $a->subAgents()->attach($b);

    CortexServer::tool(UpdateAgentTool::class, [
        'slug' => 'agent-b',
        'sub_agents' => ['agent-a'],
    ])->assertHasErrors();
});

it('deletes an agent', function () {
    Agent::factory()->create(['slug' => 'helper']);

    CortexServer::tool(DeleteAgentTool::class, ['slug' => 'helper'])
        ->assertOk()
        ->assertSee('Agent deleted.');

    expect(Agent::query()->count())->toBe(0);
});

it('lists registered tools with parity to the http payload', function () {
    app(ToolRegistry::class)->register('echo', EchoTool::class);

    $http = $this->getJson(route('cortex.tools.index'))->json('data');

    CortexServer::tool(ListToolsTool::class)
        ->assertOk()
        ->assertStructuredContent(['data' => $http]);
});
