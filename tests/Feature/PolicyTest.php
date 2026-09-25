<?php

declare(strict_types=1);

use Illuminate\Auth\GenericUser;
use Illuminate\Support\Facades\Gate;
use JayI\Cortex\CortexServiceProvider;
use JayI\Cortex\Mcp\Tools\CreatePromptVersionTool;
use JayI\Cortex\Mcp\Tools\CreateServerInstructionVersionTool;
use JayI\Cortex\Mcp\Tools\PublishPromptVersionTool;
use JayI\Cortex\Mcp\Tools\RunAgentTool;
use JayI\Cortex\Mcp\Tools\ShowPromptVersionTool;
use JayI\Cortex\Mcp\Tools\UpdatePromptTool;
use JayI\Cortex\Models\Agent;
use JayI\Cortex\Models\McpInstruction;
use JayI\Cortex\Models\McpInstructionVersion;
use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Models\PromptVersion;
use JayI\Cortex\Models\ToolDescription;
use JayI\Cortex\Models\ToolDescriptionVersion;
use JayI\Cortex\Policies\AgentPolicy;
use JayI\Cortex\Policies\McpInstructionPolicy;
use JayI\Cortex\Policies\McpInstructionVersionPolicy;
use JayI\Cortex\Policies\PromptPolicy;
use JayI\Cortex\Policies\PromptVersionPolicy;
use JayI\Cortex\Policies\ToolDescriptionPolicy;
use JayI\Cortex\Policies\ToolDescriptionVersionPolicy;
use JayI\Cortex\Runtime\DbAgent;
use JayI\Cortex\Tests\Fixtures\EchoTool;
use JayI\Cortex\Tests\Fixtures\Policies\FrozenToolDescriptionPolicy;
use JayI\Cortex\Tests\Fixtures\Policies\NoRunAgentPolicy;
use JayI\Cortex\Tests\Fixtures\Policies\ReadOnlyPromptPolicy;
use JayI\Cortex\Tests\Fixtures\Policies\SignedInMcpInstructionPolicy;
use JayI\Cortex\Tools\ToolRegistry;

/**
 * Register the policies again after a test changes `cortex.policies`, as the
 * provider does on boot.
 *
 * @param  array<class-string, class-string>  $policies
 */
function usePolicies(array $policies): void
{
    foreach ($policies as $model => $policy) {
        config()->set('cortex.policies.'.$model, $policy);
    }

    $provider = app()->getProvider(CortexServiceProvider::class);

    (fn () => $this->registerPolicies())->call($provider);
}

/**
 * A prompt with one published version.
 */
function publishedPrompt(string $slug = 'support'): Prompt
{
    $prompt = Prompt::factory()->create(['slug' => $slug]);
    $version = PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1]);
    $prompt->published_version_id = $version->getKey();
    $prompt->save();

    return $prompt;
}

it('registers the policies from the config', function (): void {
    expect(Gate::getPolicyFor(Agent::class))->toBeInstanceOf(AgentPolicy::class)
        ->and(Gate::getPolicyFor(Prompt::class))->toBeInstanceOf(PromptPolicy::class)
        ->and(Gate::getPolicyFor(PromptVersion::class))->toBeInstanceOf(PromptVersionPolicy::class)
        ->and(Gate::getPolicyFor(ToolDescription::class))->toBeInstanceOf(ToolDescriptionPolicy::class)
        ->and(Gate::getPolicyFor(ToolDescriptionVersion::class))->toBeInstanceOf(ToolDescriptionVersionPolicy::class)
        ->and(Gate::getPolicyFor(McpInstruction::class))->toBeInstanceOf(McpInstructionPolicy::class)
        ->and(Gate::getPolicyFor(McpInstructionVersion::class))->toBeInstanceOf(McpInstructionVersionPolicy::class);
});

it('allows guests and signed-in users everything by default, since nothing has an owner', function (): void {
    $agent = Agent::factory()->create();
    $prompt = publishedPrompt();
    $version = $prompt->versions()->firstOrFail();

    foreach ([null, new GenericUser(['id' => 1])] as $user) {
        $gate = Gate::forUser($user);

        expect($gate->allows('viewAny', Agent::class))->toBeTrue()
            ->and($gate->allows('create', Agent::class))->toBeTrue()
            ->and($gate->allows('view', $agent))->toBeTrue()
            ->and($gate->allows('update', $agent))->toBeTrue()
            ->and($gate->allows('delete', $agent))->toBeTrue()
            ->and($gate->allows('run', $agent))->toBeTrue()
            ->and($gate->allows('update', $prompt))->toBeTrue()
            ->and($gate->allows('viewAny', [PromptVersion::class, $prompt]))->toBeTrue()
            ->and($gate->allows('create', [PromptVersion::class, $prompt]))->toBeTrue()
            ->and($gate->allows('view', $version))->toBeTrue()
            ->and($gate->allows('publish', $version))->toBeTrue();
    }
});

it('checks versions against their parent through the Gate', function (): void {
    usePolicies([Prompt::class => ReadOnlyPromptPolicy::class]);

    $prompt = publishedPrompt();
    $version = $prompt->versions()->firstOrFail();

    expect(Gate::allows('viewAny', [PromptVersion::class, $prompt]))->toBeTrue()
        ->and(Gate::allows('view', $version))->toBeTrue()
        ->and(Gate::allows('create', [PromptVersion::class, $prompt]))->toBeFalse()
        ->and(Gate::allows('publish', $version))->toBeFalse();
});

it('uses a prompt policy swapped in the config, for prompts and their versions', function (): void {
    usePolicies([Prompt::class => ReadOnlyPromptPolicy::class]);

    publishedPrompt();

    $this->getJson(route('cortex.prompts.show', 'support'))->assertOk();
    $this->getJson(route('cortex.prompts.versions.show', ['support', 1]))->assertOk();
    $this->patchJson(route('cortex.prompts.update', 'support'), ['name' => 'Renamed'])->assertForbidden();
    $this->postJson(route('cortex.prompts.versions.store', 'support'), ['content' => 'New.'])->assertForbidden();
    $this->postJson(route('cortex.prompts.versions.publish', ['support', 1]))->assertForbidden();

    mcpTool(ShowPromptVersionTool::class, ['slug' => 'support', 'version' => 1])->assertOk();
    mcpTool(UpdatePromptTool::class, ['slug' => 'support', 'name' => 'Renamed'])->assertHasErrors(['Unauthorized.']);
    mcpTool(CreatePromptVersionTool::class, ['slug' => 'support', 'content' => 'New.'])->assertHasErrors(['Unauthorized.']);
    mcpTool(PublishPromptVersionTool::class, ['slug' => 'support', 'version' => 1])->assertHasErrors(['Unauthorized.']);

    expect(PromptVersion::query()->count())->toBe(1)
        ->and(Prompt::query()->value('name'))->not->toBe('Renamed');
});

it('checks the custom run ability on agents over HTTP and MCP', function (): void {
    usePolicies([Agent::class => NoRunAgentPolicy::class]);

    DbAgent::fake(['Hello.']);
    Agent::factory()->create(['slug' => 'helper']);

    $this->getJson(route('cortex.agents.show', 'helper'))->assertOk();
    $this->postJson(route('cortex.agents.run', 'helper'), ['input' => 'Hi'])->assertForbidden();

    mcpTool(RunAgentTool::class, ['slug' => 'helper', 'input' => 'Hi'])->assertHasErrors(['Unauthorized.']);

    DbAgent::assertNeverPrompted();
});

it('checks the first version of an override against the override policy', function (): void {
    usePolicies([ToolDescription::class => FrozenToolDescriptionPolicy::class]);

    app(ToolRegistry::class)->register('echo', EchoTool::class);

    $this->postJson(route('cortex.tools.description.versions.store', 'echo'), ['content' => 'Echo.'])
        ->assertForbidden();

    expect(ToolDescription::query()->count())->toBe(0);
});

it('passes the signed-in user to the policy over HTTP and MCP', function (): void {
    usePolicies([McpInstruction::class => SignedInMcpInstructionPolicy::class]);

    $this->postJson(route('cortex.servers.instructions.versions.store', 'cortex'), ['content' => 'Guest.'])
        ->assertForbidden();
    mcpTool(CreateServerInstructionVersionTool::class, ['server' => 'cortex', 'content' => 'Guest.'])
        ->assertHasErrors(['Unauthorized.']);

    $this->actingAs(new GenericUser(['id' => 1]));

    $this->postJson(route('cortex.servers.instructions.versions.store', 'cortex'), ['content' => 'Member.'])
        ->assertCreated();
    mcpTool(CreateServerInstructionVersionTool::class, ['server' => 'cortex', 'content' => 'Member again.'])
        ->assertOk();

    expect(McpInstructionVersion::query()->count())->toBe(2);
});
