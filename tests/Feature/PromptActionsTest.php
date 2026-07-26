<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use JayI\Cortex\Actions\CreatePromptAction;
use JayI\Cortex\Actions\CreatePromptVersionAction;
use JayI\Cortex\Actions\DeletePromptAction;
use JayI\Cortex\Actions\ListPromptsAction;
use JayI\Cortex\Actions\ListPromptVersionsAction;
use JayI\Cortex\Actions\PublishPromptVersionAction;
use JayI\Cortex\Actions\ShowPromptVersionAction;
use JayI\Cortex\Actions\UpdatePromptAction;
use JayI\Cortex\Models\Agent;
use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Models\PromptVersion;

it('creates a prompt with version 1 published by default', function () {
    $prompt = app(CreatePromptAction::class)->execute([
        'name' => 'Support',
        'slug' => 'support',
        'content' => 'You are helpful.',
    ]);

    expect($prompt->versions()->count())->toBe(1)
        ->and($prompt->publishedVersion?->version)->toBe(1)
        ->and($prompt->publishedVersion?->content)->toBe('You are helpful.');
});

it('creates a prompt without publishing when requested', function () {
    $prompt = app(CreatePromptAction::class)->execute([
        'name' => 'Draft',
        'slug' => 'draft',
        'content' => 'Draft content.',
        'publish' => false,
    ]);

    expect($prompt->published_version_id)->toBeNull()
        ->and($prompt->versions()->count())->toBe(1);
});

it('creates sequential immutable versions', function () {
    $prompt = Prompt::factory()->create();
    PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1]);

    $version = app(CreatePromptVersionAction::class)->execute($prompt, ['content' => 'v2']);

    expect($version->version)->toBe(2)
        ->and($prompt->refresh()->published_version_id)->toBeNull();
});

it('publishes a new version when requested at creation', function () {
    $prompt = Prompt::factory()->create();

    $version = app(CreatePromptVersionAction::class)->execute($prompt, [
        'content' => 'v1',
        'publish' => true,
    ]);

    expect($prompt->refresh()->published_version_id)->toBe($version->getKey());
});

it('publishes an existing version by number', function () {
    $prompt = Prompt::factory()->create();
    PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1]);
    $v2 = PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 2]);

    $result = app(PublishPromptVersionAction::class)->execute($prompt, 2);

    expect($result->published_version_id)->toBe($v2->getKey());
});

it('refuses to publish a version that does not exist', function () {
    $prompt = Prompt::factory()->create();

    app(PublishPromptVersionAction::class)->execute($prompt, 9);
})->throws(ModelNotFoundException::class);

it('shows a version by number', function () {
    $prompt = Prompt::factory()->create();
    $version = PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 3]);

    expect(app(ShowPromptVersionAction::class)->execute($prompt, 3)->getKey())
        ->toBe($version->getKey());
});

it('updates prompt metadata only', function () {
    $prompt = Prompt::factory()->create(['name' => 'Old']);

    $updated = app(UpdatePromptAction::class)->execute($prompt, [
        'name' => 'New',
        'description' => 'Updated.',
    ]);

    expect($updated->name)->toBe('New')
        ->and($updated->description)->toBe('Updated.');
});

it('deletes a prompt with its versions', function () {
    $prompt = Prompt::factory()->create();
    PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1]);

    app(DeletePromptAction::class)->execute($prompt);

    expect(Prompt::query()->count())->toBe(0)
        ->and(PromptVersion::query()->count())->toBe(0);
});

it('refuses to delete a prompt attached to agents', function () {
    $prompt = Prompt::factory()->create();
    Agent::factory()->create(['prompt_id' => $prompt->getKey()]);

    app(DeletePromptAction::class)->execute($prompt);
})->throws(ValidationException::class);

it('lists prompts with published versions', function () {
    Prompt::factory()->count(3)->create();

    $prompts = app(ListPromptsAction::class)->execute();

    expect($prompts->total())->toBe(3);
});

it('lists versions newest first', function () {
    $prompt = Prompt::factory()->create();
    PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 1]);
    PromptVersion::factory()->for($prompt, 'prompt')->create(['version' => 2]);

    $versions = app(ListPromptVersionsAction::class)->execute($prompt);

    expect($versions->total())->toBe(2)
        ->and($versions->items()[0]->version)->toBe(2);
});
