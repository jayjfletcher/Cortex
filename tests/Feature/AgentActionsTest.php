<?php

declare(strict_types=1);

use Illuminate\Validation\ValidationException;
use JayI\Cortex\Actions\CreateAgentAction;
use JayI\Cortex\Actions\DeleteAgentAction;
use JayI\Cortex\Actions\ListAgentsAction;
use JayI\Cortex\Actions\ShowAgentAction;
use JayI\Cortex\Actions\UpdateAgentAction;
use JayI\Cortex\Models\Agent;
use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Models\PromptVersion;
use JayI\Cortex\Tests\Fixtures\EchoTool;
use JayI\Cortex\Tools\ToolRegistry;

it('creates an agent with tools, prompt, and sub-agents', function () {
    app(ToolRegistry::class)->register('echo', EchoTool::class);
    $prompt = Prompt::factory()->create(['slug' => 'support']);
    PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1]);
    $sub = Agent::factory()->create(['slug' => 'researcher']);

    $agent = app(CreateAgentAction::class)->execute([
        'name' => 'Coordinator',
        'slug' => 'coordinator',
        'provider' => 'anthropic',
        'model' => 'claude-sonnet-5',
        'settings' => ['temperature' => 0.3],
        'tools' => ['echo'],
        'prompt' => 'support',
        'prompt_version' => 1,
        'sub_agents' => ['researcher'],
    ]);

    expect($agent->tools)->toBe(['echo'])
        ->and($agent->prompt?->slug)->toBe('support')
        ->and($agent->pinnedVersion?->version)->toBe(1)
        ->and($agent->subAgents->pluck('slug')->all())->toBe([$sub->slug])
        ->and($agent->provider)->toBe('anthropic')
        ->and($agent->settings)->toBe(['temperature' => 0.3]);
});

it('rejects pinning a version without a prompt', function () {
    app(CreateAgentAction::class)->execute([
        'name' => 'Broken',
        'slug' => 'broken',
        'prompt_version' => 1,
    ]);
})->throws(ValidationException::class);

it('updates an agent with whole-list sync semantics', function () {
    $prompt = Prompt::factory()->create(['slug' => 'support']);
    PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1]);
    $agent = Agent::factory()->create([
        'prompt_id' => $prompt->getKey(),
        'tools' => ['old-tool'],
    ]);
    $agent->subAgents()->attach(Agent::factory()->create());
    app(ToolRegistry::class)->register('echo', EchoTool::class);

    $updated = app(UpdateAgentAction::class)->execute($agent, [
        'tools' => ['echo'],
        'sub_agents' => [],
        'prompt' => null,
    ]);

    expect($updated->tools)->toBe(['echo'])
        ->and($updated->subAgents)->toHaveCount(0)
        ->and($updated->prompt_id)->toBeNull()
        ->and($updated->prompt_version_id)->toBeNull();
});

it('clears a stale pin when the prompt changes without a new pin', function () {
    $old = Prompt::factory()->create();
    $oldVersion = PromptVersion::factory()->for($old, 'prompt')->create(['version' => 1]);
    $new = Prompt::factory()->create(['slug' => 'newer']);
    $agent = Agent::factory()->create([
        'prompt_id' => $old->getKey(),
        'prompt_version_id' => $oldVersion->getKey(),
    ]);

    $updated = app(UpdateAgentAction::class)->execute($agent, ['prompt' => 'newer']);

    expect($updated->prompt?->slug)->toBe('newer')
        ->and($updated->prompt_version_id)->toBeNull();
});

it('rejects sub-agent cycles', function () {
    $a = Agent::factory()->create(['slug' => 'agent-a']);
    $b = Agent::factory()->create(['slug' => 'agent-b']);
    $a->subAgents()->attach($b);

    app(UpdateAgentAction::class)->execute($b, ['sub_agents' => ['agent-a']]);
})->throws(ValidationException::class, 'circular');

it('rejects an agent as its own sub-agent', function () {
    $agent = Agent::factory()->create(['slug' => 'self']);

    app(UpdateAgentAction::class)->execute($agent, ['sub_agents' => ['self']]);
})->throws(ValidationException::class, 'circular');

it('deletes an agent and its sub-agent links', function () {
    $agent = Agent::factory()->create();
    $agent->subAgents()->attach($sub = Agent::factory()->create());

    app(DeleteAgentAction::class)->execute($agent);

    expect(Agent::query()->whereKey($agent->getKey())->exists())->toBeFalse()
        ->and($sub->refresh()->parentAgents)->toHaveCount(0);
});

it('lists and shows agents with relations', function () {
    $agent = Agent::factory()->create();

    expect(app(ListAgentsAction::class)->execute()->total())->toBe(1)
        ->and(app(ShowAgentAction::class)->execute($agent)->relationLoaded('subAgents'))->toBeTrue();
});
