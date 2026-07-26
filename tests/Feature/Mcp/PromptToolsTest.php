<?php

declare(strict_types=1);

use Illuminate\Testing\Fluent\AssertableJson;
use JayI\Cortex\Mcp\CortexServer;
use JayI\Cortex\Mcp\Tools\CreatePromptTool;
use JayI\Cortex\Mcp\Tools\CreatePromptVersionTool;
use JayI\Cortex\Mcp\Tools\DeletePromptTool;
use JayI\Cortex\Mcp\Tools\ListPromptsTool;
use JayI\Cortex\Mcp\Tools\ListPromptVersionsTool;
use JayI\Cortex\Mcp\Tools\PublishPromptVersionTool;
use JayI\Cortex\Mcp\Tools\ShowPromptTool;
use JayI\Cortex\Mcp\Tools\ShowPromptVersionTool;
use JayI\Cortex\Mcp\Tools\UpdatePromptTool;
use JayI\Cortex\Models\Agent;
use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Models\PromptVersion;

it('creates a prompt with parity to the http payload', function () {
    $mcp = CortexServer::tool(CreatePromptTool::class, [
        'name' => 'Support',
        'slug' => 'support',
        'content' => 'You are helpful.',
    ])->assertOk();

    $http = $this->getJson(route('cortex.prompts.show', 'support'))->json('data');

    $mcp->assertStructuredContent($http);
});

it('lists prompts in a data envelope', function () {
    $prompt = Prompt::factory()->create();
    $version = PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1]);
    $prompt->published_version_id = $version->getKey();
    $prompt->save();

    CortexServer::tool(ListPromptsTool::class)
        ->assertOk()
        ->assertStructuredContent(
            fn (AssertableJson $json) => $json
                ->count('data', 1)
                ->where('data.0.slug', $prompt->slug)
                ->where('data.0.published_version.version', 1)
                ->etc(),
        );
});

it('lists zero prompts without erroring', function () {
    CortexServer::tool(ListPromptsTool::class)
        ->assertOk()
        ->assertStructuredContent(['data' => []]);
});

it('shows a prompt by slug', function () {
    Prompt::factory()->create(['slug' => 'support']);

    CortexServer::tool(ShowPromptTool::class, ['slug' => 'support'])
        ->assertOk()
        ->assertSee('support');
});

it('errors not found for unknown prompt slugs', function () {
    CortexServer::tool(ShowPromptTool::class, ['slug' => 'missing'])
        ->assertHasErrors(['Not found.']);
});

it('validates create prompt input', function () {
    CortexServer::tool(CreatePromptTool::class, ['name' => 'X'])
        ->assertHasErrors();
});

it('updates prompt metadata', function () {
    Prompt::factory()->create(['slug' => 'support', 'name' => 'Old']);

    CortexServer::tool(UpdatePromptTool::class, ['slug' => 'support', 'name' => 'New'])
        ->assertOk()
        ->assertSee('New');
});

it('deletes a prompt', function () {
    Prompt::factory()->create(['slug' => 'support']);

    CortexServer::tool(DeletePromptTool::class, ['slug' => 'support'])
        ->assertOk()
        ->assertSee('Prompt deleted.');

    expect(Prompt::query()->count())->toBe(0);
});

it('refuses to delete prompts attached to agents', function () {
    $prompt = Prompt::factory()->create(['slug' => 'support']);
    Agent::factory()->create(['prompt_id' => $prompt->getKey()]);

    CortexServer::tool(DeletePromptTool::class, ['slug' => 'support'])
        ->assertHasErrors();
});

it('creates and lists versions', function () {
    $prompt = Prompt::factory()->create(['slug' => 'support']);
    PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1]);

    CortexServer::tool(CreatePromptVersionTool::class, ['slug' => 'support', 'content' => 'v2'])
        ->assertOk();

    CortexServer::tool(ListPromptVersionsTool::class, ['slug' => 'support'])
        ->assertOk()
        ->assertStructuredContent(
            fn (AssertableJson $json) => $json
                ->count('data', 2)
                ->where('data.0.version', 2)
                ->etc(),
        );
});

it('shows and publishes a version by number', function () {
    $prompt = Prompt::factory()->create(['slug' => 'support']);
    PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1, 'content' => 'v1']);

    CortexServer::tool(ShowPromptVersionTool::class, ['slug' => 'support', 'version' => 1])
        ->assertOk()
        ->assertSee('v1');

    CortexServer::tool(PublishPromptVersionTool::class, ['slug' => 'support', 'version' => 1])
        ->assertOk();

    expect($prompt->refresh()->publishedVersion?->version)->toBe(1);
});
