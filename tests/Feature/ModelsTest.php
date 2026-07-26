<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use JayI\Cortex\Models\Agent;
use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Models\PromptVersion;

it('relates prompts to versions and a published version', function () {
    $prompt = Prompt::factory()->create();
    $version = PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1]);

    $prompt->published_version_id = $version->getKey();
    $prompt->save();

    expect($prompt->versions)->toHaveCount(1)
        ->and($prompt->refresh()->publishedVersion?->getKey())->toBe($version->getKey())
        ->and($version->prompt?->getKey())->toBe($prompt->getKey());
});

it('enforces prompt version immutability', function () {
    $version = PromptVersion::factory()->create();

    $version->update(['content' => 'changed']);
})->throws(LogicException::class, 'Prompt versions are immutable.');

it('enforces one version number per prompt', function () {
    $prompt = Prompt::factory()->create();

    PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1]);
    PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1]);
})->throws(QueryException::class);

it('deletes versions when the prompt is deleted', function () {
    $prompt = Prompt::factory()->create();
    PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1]);

    $prompt->delete();

    expect(PromptVersion::query()->count())->toBe(0);
});

it('casts agent settings and tools to arrays', function () {
    $agent = Agent::factory()->create([
        'settings' => ['temperature' => 0.5],
        'tools' => ['echo'],
    ]);

    expect($agent->refresh())
        ->settings->toBe(['temperature' => 0.5])
        ->tools->toBe(['echo']);
});

it('defaults agent tools to an empty array', function () {
    $agent = Agent::factory()->create();

    expect($agent->refresh()->tools)->toBe([]);
});

it('relates agents to prompts and pinned versions', function () {
    $prompt = Prompt::factory()->create();
    $version = PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1]);

    $agent = Agent::factory()->create([
        'prompt_id' => $prompt->getKey(),
        'prompt_version_id' => $version->getKey(),
    ]);

    expect($agent->prompt?->getKey())->toBe($prompt->getKey())
        ->and($agent->pinnedVersion?->getKey())->toBe($version->getKey())
        ->and($prompt->agents()->count())->toBe(1);
});

it('relates agents to sub-agents in both directions', function () {
    $parent = Agent::factory()->create();
    $child = Agent::factory()->create();

    $parent->subAgents()->attach($child);

    expect($parent->subAgents->pluck('id')->all())->toBe([$child->getKey()])
        ->and($child->parentAgents->pluck('id')->all())->toBe([$parent->getKey()]);
});

it('detaches sub-agent links when an agent is deleted', function () {
    $parent = Agent::factory()->create();
    $child = Agent::factory()->create();
    $parent->subAgents()->attach($child);

    $child->delete();

    expect($parent->subAgents()->count())->toBe(0);
});
