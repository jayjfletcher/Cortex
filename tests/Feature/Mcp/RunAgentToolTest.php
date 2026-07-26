<?php

declare(strict_types=1);

use Illuminate\Testing\Fluent\AssertableJson;
use JayI\Cortex\Mcp\CortexServer;
use JayI\Cortex\Mcp\Tools\RunAgentTool;
use JayI\Cortex\Models\Agent;
use JayI\Cortex\Runtime\DbAgent;

it('runs an agent and returns text with usage', function () {
    DbAgent::fake(['Hello from the agent.']);
    Agent::factory()->create(['slug' => 'helper']);

    CortexServer::tool(RunAgentTool::class, ['slug' => 'helper', 'input' => 'Hi'])
        ->assertOk()
        ->assertStructuredContent(
            fn (AssertableJson $json) => $json
                ->where('text', 'Hello from the agent.')
                ->has('usage.prompt_tokens')
                ->etc(),
        );

    DbAgent::assertPrompted(fn ($prompt): bool => $prompt->prompt === 'Hi');
});

it('validates run input', function () {
    Agent::factory()->create(['slug' => 'helper']);

    CortexServer::tool(RunAgentTool::class, ['slug' => 'helper'])
        ->assertHasErrors();
});

it('errors not found for unknown agents', function () {
    CortexServer::tool(RunAgentTool::class, ['slug' => 'missing', 'input' => 'Hi'])
        ->assertHasErrors(['Not found.']);
});
