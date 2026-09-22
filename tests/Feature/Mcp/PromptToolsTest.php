<?php

declare(strict_types=1);

use Illuminate\Testing\Fluent\AssertableJson;
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
    $mcp = mcpTool(CreatePromptTool::class, [
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

    mcpTool(ListPromptsTool::class)
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
    mcpTool(ListPromptsTool::class)
        ->assertOk()
        ->assertStructuredContent(['data' => []]);
});

it('shows a prompt by slug', function () {
    Prompt::factory()->create(['slug' => 'support']);

    mcpTool(ShowPromptTool::class, ['slug' => 'support'])
        ->assertOk()
        ->assertSee('support');
});

it('errors not found for unknown prompt slugs', function () {
    mcpTool(ShowPromptTool::class, ['slug' => 'missing'])
        ->assertHasErrors(['Not found.']);
});

it('validates create prompt input', function () {
    mcpTool(CreatePromptTool::class, ['name' => 'X'])
        ->assertHasErrors();
});

it('updates prompt metadata', function () {
    Prompt::factory()->create(['slug' => 'support', 'name' => 'Old']);

    mcpTool(UpdatePromptTool::class, ['slug' => 'support', 'name' => 'New'])
        ->assertOk()
        ->assertSee('New');
});

it('deletes a prompt', function () {
    Prompt::factory()->create(['slug' => 'support']);

    mcpTool(DeletePromptTool::class, ['slug' => 'support'])
        ->assertOk()
        ->assertSee('Prompt deleted.');

    expect(Prompt::query()->count())->toBe(0);
});

it('refuses to delete prompts attached to agents', function () {
    $prompt = Prompt::factory()->create(['slug' => 'support']);
    Agent::factory()->create(['prompt_id' => $prompt->getKey()]);

    mcpTool(DeletePromptTool::class, ['slug' => 'support'])
        ->assertHasErrors();
});

it('creates and lists versions', function () {
    $prompt = Prompt::factory()->create(['slug' => 'support']);
    PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1]);

    mcpTool(CreatePromptVersionTool::class, ['slug' => 'support', 'content' => 'v2'])
        ->assertOk();

    mcpTool(ListPromptVersionsTool::class, ['slug' => 'support'])
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

    mcpTool(ShowPromptVersionTool::class, ['slug' => 'support', 'version' => 1])
        ->assertOk()
        ->assertSee('v1');

    mcpTool(PublishPromptVersionTool::class, ['slug' => 'support', 'version' => 1])
        ->assertOk();

    expect($prompt->refresh()->publishedVersion?->version)->toBe(1);
});
