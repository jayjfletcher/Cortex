<?php

declare(strict_types=1);

use JayI\Cortex\Models\Agent;
use JayI\Cortex\Runtime\DbAgent;

it('runs an agent and returns text with usage', function () {
    DbAgent::fake(['Hello from the agent.']);
    Agent::factory()->create(['slug' => 'helper']);

    $this->postJson(route('cortex.agents.run', 'helper'), ['input' => 'Hi'])
        ->assertOk()
        ->assertJsonPath('data.text', 'Hello from the agent.')
        ->assertJsonStructure(['data' => ['text', 'usage']]);

    DbAgent::assertPrompted(fn ($prompt): bool => $prompt->prompt === 'Hi');
});

it('validates run input', function () {
    Agent::factory()->create(['slug' => 'helper']);

    $this->postJson(route('cortex.agents.run', 'helper'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['input']);
});

it('returns 404 when running unknown agents', function () {
    $this->postJson(route('cortex.agents.run', 'missing'), ['input' => 'Hi'])->assertNotFound();
});
