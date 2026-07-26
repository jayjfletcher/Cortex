<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use JayI\Cortex\Actions\PublishPromptVersionAction;
use JayI\Cortex\Actions\RunAgentAction;
use JayI\Cortex\Exceptions\CircularAgentReferenceException;
use JayI\Cortex\Exceptions\PromptNotPublishedException;
use JayI\Cortex\Exceptions\ToolNotFoundException;
use JayI\Cortex\Facades\Cortex;
use JayI\Cortex\Models\Agent;
use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Models\PromptVersion;
use JayI\Cortex\Runtime\AgentFactory;
use JayI\Cortex\Runtime\DbAgent;
use JayI\Cortex\Tests\Fixtures\EchoTool;
use JayI\Cortex\Tools\ToolRegistry;

it('builds an agent from pinned version instructions', function () {
    $prompt = Prompt::factory()->create();
    $v1 = PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1, 'content' => 'Pinned.']);
    PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 2, 'content' => 'Published.']);
    app(PublishPromptVersionAction::class)->execute($prompt, 2);

    $agent = Agent::factory()->create([
        'prompt_id' => $prompt->getKey(),
        'prompt_version_id' => $v1->getKey(),
    ]);

    expect((string) app(AgentFactory::class)->make($agent)->instructions())->toBe('Pinned.');
});

it('builds an agent from the published version when unpinned', function () {
    $prompt = Prompt::factory()->create();
    $version = PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1, 'content' => 'Published.']);
    $prompt->published_version_id = $version->getKey();
    $prompt->save();

    $agent = Agent::factory()->create(['prompt_id' => $prompt->getKey()]);

    expect((string) app(AgentFactory::class)->make($agent)->instructions())->toBe('Published.');
});

it('throws when the attached prompt has no published version', function () {
    $prompt = Prompt::factory()->create();
    $agent = Agent::factory()->create(['prompt_id' => $prompt->getKey()]);

    app(AgentFactory::class)->make($agent);
})->throws(PromptNotPublishedException::class);

it('uses empty instructions without a prompt', function () {
    $agent = Agent::factory()->create();

    expect((string) app(AgentFactory::class)->make($agent)->instructions())->toBe('');
});

it('resolves registered tools and sub-agents onto the runtime agent', function () {
    app(ToolRegistry::class)->register('echo', EchoTool::class);
    $agent = Agent::factory()->create(['tools' => ['echo']]);
    $agent->subAgents()->attach(Agent::factory()->create());

    $tools = iterator_to_array(collect(app(AgentFactory::class)->make($agent)->tools())->getIterator());

    expect($tools)->toHaveCount(2)
        ->and($tools[0])->toBeInstanceOf(EchoTool::class)
        ->and($tools[1])->toBeInstanceOf(DbAgent::class);
});

it('throws for unregistered tool names', function () {
    $agent = Agent::factory()->create(['tools' => ['missing']]);

    app(AgentFactory::class)->make($agent);
})->throws(ToolNotFoundException::class);

it('guards against circular sub-agent graphs at build time', function () {
    $a = Agent::factory()->create();
    $b = Agent::factory()->create();
    $a->subAgents()->attach($b);
    DB::table('cortex_agent_agent')->insert([
        'agent_id' => $b->getKey(),
        'sub_agent_id' => $a->getKey(),
    ]);

    app(AgentFactory::class)->make($a);
})->throws(CircularAgentReferenceException::class);

it('exposes provider, model, and settings to the ai sdk', function () {
    $agent = Agent::factory()->create([
        'provider' => 'anthropic',
        'model' => 'claude-sonnet-5',
        'settings' => ['temperature' => 0.3, 'max_steps' => 5, 'max_tokens' => 1000, 'top_p' => 0.9],
    ]);

    $runtime = app(AgentFactory::class)->make($agent);

    expect($runtime->provider())->toBe('anthropic')
        ->and($runtime->model())->toBe('claude-sonnet-5')
        ->and($runtime->temperature())->toBe(0.3)
        ->and($runtime->maxSteps())->toBe(5)
        ->and($runtime->maxTokens())->toBe(1000)
        ->and($runtime->topP())->toBe(0.9);
});

it('runs an agent through the run action', function () {
    DbAgent::fake(['Hello from the agent.']);
    $agent = Agent::factory()->create();

    $response = app(RunAgentAction::class)->execute($agent, 'Hi');

    expect($response->text)->toBe('Hello from the agent.');
    DbAgent::assertPrompted(fn ($prompt): bool => $prompt->prompt === 'Hi');
});

it('runs an agent through the manager by slug', function () {
    DbAgent::fake(['Managed.']);
    Agent::factory()->create(['slug' => 'helper']);

    expect(Cortex::run('helper', 'Hi')->text)->toBe('Managed.');
});
