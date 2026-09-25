<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use JayI\Cortex\Actions\CreateAgentAction;
use JayI\Cortex\Actions\CreateMcpInstructionVersionAction;
use JayI\Cortex\Actions\CreatePromptAction;
use JayI\Cortex\Actions\CreatePromptVersionAction;
use JayI\Cortex\Actions\CreateToolDescriptionVersionAction;
use JayI\Cortex\Actions\DeleteAgentAction;
use JayI\Cortex\Actions\DeleteMcpInstructionAction;
use JayI\Cortex\Actions\DeletePromptAction;
use JayI\Cortex\Actions\DeleteToolDescriptionAction;
use JayI\Cortex\Actions\ListAgentsAction;
use JayI\Cortex\Actions\ListMcpInstructionVersionsAction;
use JayI\Cortex\Actions\ListMcpServersAction;
use JayI\Cortex\Actions\ListPromptsAction;
use JayI\Cortex\Actions\ListPromptVersionsAction;
use JayI\Cortex\Actions\ListProvidersAction;
use JayI\Cortex\Actions\ListToolDescriptionVersionsAction;
use JayI\Cortex\Actions\ListToolsAction;
use JayI\Cortex\Actions\PublishMcpInstructionVersionAction;
use JayI\Cortex\Actions\PublishPromptVersionAction;
use JayI\Cortex\Actions\PublishToolDescriptionVersionAction;
use JayI\Cortex\Actions\RunAgentAction;
use JayI\Cortex\Actions\ShowAgentAction;
use JayI\Cortex\Actions\ShowMcpInstructionAction;
use JayI\Cortex\Actions\ShowPromptAction;
use JayI\Cortex\Actions\ShowPromptVersionAction;
use JayI\Cortex\Actions\ShowToolDescriptionAction;
use JayI\Cortex\Actions\UpdateAgentAction;
use JayI\Cortex\Actions\UpdatePromptAction;
use JayI\Cortex\Contracts\ActionFinishedEvent;
use JayI\Cortex\Contracts\ActionStartingEvent;
use JayI\Cortex\Contracts\ModelLifecycleEvent;
use JayI\Cortex\Events\Action\AgentRanActionEvent;
use JayI\Cortex\Events\Action\PromptCreatedActionEvent;
use JayI\Cortex\Events\Action\PromptCreatingActionEvent;
use JayI\Cortex\Events\Action\PromptDeletedActionEvent;
use JayI\Cortex\Events\Action\PromptDeletingActionEvent;
use JayI\Cortex\Events\Model\AgentCreatingEvent;
use JayI\Cortex\Events\Model\PromptVersionCreatedEvent;
use JayI\Cortex\Models\Agent;
use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Runtime\DbAgent;

/**
 * Record every event of a kind, in order.
 *
 * @param  class-string  $kind
 * @return ArrayObject<int, object>
 */
function recordEvents(string $kind): ArrayObject
{
    /** @var ArrayObject<int, object> $seen */
    $seen = new ArrayObject;

    Event::listen($kind, function (object $event) use ($seen): void {
        $seen->append($event);
    });

    return $seen;
}

it('fires every lifecycle event of an agent', function (): void {
    $seen = recordEvents(ModelLifecycleEvent::class);

    $agent = Agent::factory()->create();
    $agent->name = 'Renamed';
    $agent->save();
    Agent::query()->find($agent->id);
    $agent->replicate();
    $agent->delete();

    $hooks = collect($seen)
        ->filter(fn (ModelLifecycleEvent $event): bool => $event->model() instanceof Agent)
        ->map(fn (ModelLifecycleEvent $event): string => $event->hook())
        ->unique()
        ->values()
        ->all();

    expect($hooks)->toEqualCanonicalizing([
        'retrieved', 'creating', 'created', 'updating', 'updated', 'saving', 'saved',
        'deleting', 'deleted', 'replicating',
    ]);
});

it('fires the lifecycle events of prompts, overrides and their versions', function (): void {
    $seen = recordEvents(ModelLifecycleEvent::class);

    app(CreatePromptAction::class)->execute(['name' => 'Support', 'slug' => 'support', 'content' => 'Be kind.']);
    app(CreateToolDescriptionVersionAction::class)->execute('echo', ['content' => 'Echo.', 'publish' => true]);
    app(CreateMcpInstructionVersionAction::class)->execute('cortex', ['content' => 'Use tools.', 'publish' => true]);

    $models = collect($seen)
        ->map(fn (ModelLifecycleEvent $event): string => class_basename($event->model()).'.'.$event->hook())
        ->unique();

    expect($models)->toContain(
        'Prompt.creating', 'Prompt.created', 'Prompt.updated', 'PromptVersion.created',
        'ToolDescription.created', 'ToolDescription.updated', 'ToolDescriptionVersion.created',
        'McpInstruction.created', 'McpInstruction.updated', 'McpInstructionVersion.created',
    );
});

it('carries the model on the event', function (): void {
    Event::fake([PromptVersionCreatedEvent::class]);

    $prompt = app(CreatePromptAction::class)->execute(['name' => 'Support', 'slug' => 'support', 'content' => 'Be kind.']);

    Event::assertDispatched(
        PromptVersionCreatedEvent::class,
        fn (PromptVersionCreatedEvent $event): bool => $event->version->prompt_id === $prompt->id && $event->model() === $event->version,
    );
});

it('lets a creating listener stop an agent being created', function (): void {
    Event::listen(AgentCreatingEvent::class, fn (): bool => false);

    $agent = new Agent(['name' => 'Helper', 'slug' => 'helper']);

    expect($agent->save())->toBeFalse()
        ->and(Agent::query()->count())->toBe(0);
});

it('starts and finishes every action once, in order', function (): void {
    $starts = recordEvents(ActionStartingEvent::class);
    $stops = recordEvents(ActionFinishedEvent::class);

    DbAgent::fake(['Hello.']);

    $prompt = app(CreatePromptAction::class)->execute(['name' => 'Support', 'slug' => 'support', 'content' => 'Be kind.']);
    app(CreatePromptVersionAction::class)->execute($prompt, ['content' => 'Be kinder.']);
    app(PublishPromptVersionAction::class)->execute($prompt, 2);
    app(UpdatePromptAction::class)->execute($prompt, ['name' => 'Support desk']);
    app(ShowPromptAction::class)->execute($prompt);
    app(ShowPromptVersionAction::class)->execute($prompt, 1);
    app(ListPromptsAction::class)->execute();
    app(ListPromptVersionsAction::class)->execute($prompt);

    $agent = app(CreateAgentAction::class)->execute(['name' => 'Helper', 'slug' => 'helper']);
    app(UpdateAgentAction::class)->execute($agent, ['description' => 'Helps.']);
    app(ShowAgentAction::class)->execute($agent);
    app(ListAgentsAction::class)->execute();
    app(RunAgentAction::class)->execute($agent, 'Hi');
    app(DeleteAgentAction::class)->execute($agent);
    app(DeletePromptAction::class)->execute($prompt);

    app(CreateToolDescriptionVersionAction::class)->execute('echo', ['content' => 'Echo.']);
    $description = app(ShowToolDescriptionAction::class)->execute('echo');
    app(PublishToolDescriptionVersionAction::class)->execute($description, 1);
    app(ListToolDescriptionVersionsAction::class)->execute($description);
    app(DeleteToolDescriptionAction::class)->execute($description);

    app(CreateMcpInstructionVersionAction::class)->execute('cortex', ['content' => 'Use tools.']);
    $instruction = app(ShowMcpInstructionAction::class)->execute('cortex');
    app(PublishMcpInstructionVersionAction::class)->execute($instruction, 1);
    app(ListMcpInstructionVersionsAction::class)->execute($instruction);
    app(DeleteMcpInstructionAction::class)->execute($instruction);

    app(ListToolsAction::class)->execute();
    app(ListMcpServersAction::class)->execute();
    app(ListProvidersAction::class)->execute();

    $started = collect($starts)->map(fn (object $event): string => class_basename($event))->unique();
    $finished = collect($stops)->map(fn (object $event): string => class_basename($event))->unique();

    expect($started)->toHaveCount(28)
        ->and($finished)->toHaveCount(28)
        ->and(count($starts))->toBe(count($stops))
        ->and($started->all())->toContain(
            'PromptCreatingActionEvent', 'PromptVersionCreatingActionEvent', 'PromptVersionPublishingActionEvent',
            'AgentRunningActionEvent', 'ToolDescriptionVersionCreatingActionEvent', 'McpInstructionVersionPublishingActionEvent',
        )
        ->and($finished->all())->toContain('AgentRanActionEvent', 'PromptDeletedActionEvent', 'McpServersListedActionEvent');
});

it('gives every action exactly one start and one finish event', function (): void {
    $actions = glob(dirname(__DIR__, 2).'/src/Actions/*Action.php') ?: [];

    $unpaired = [];

    foreach ($actions as $path) {
        $source = (string) file_get_contents($path);
        preg_match_all('/([A-Za-z]+ActionEvent)::dispatch/', $source, $matches);

        $kinds = array_map(
            fn (string $event): string => is_subclass_of('JayI\\Cortex\\Events\\Action\\'.$event, ActionStartingEvent::class) ? 'start' : 'finish',
            $matches[1],
        );

        sort($kinds);

        if ($kinds !== ['finish', 'start']) {
            $unpaired[] = basename($path, '.php');
        }
    }

    expect($actions)->toHaveCount(28)
        ->and($unpaired)->toBe([]);
});

it('carries the input on the start event and the result on the finish event', function (): void {
    DbAgent::fake(['Hello.']);
    $agent = Agent::factory()->create();

    Event::fake([AgentRanActionEvent::class]);

    app(RunAgentAction::class)->execute($agent, 'Hi');

    Event::assertDispatched(
        AgentRanActionEvent::class,
        fn (AgentRanActionEvent $event): bool => $event->agent->is($agent) && $event->input === 'Hi' && $event->response->text === 'Hello.',
    );
});

it('starts before the work and finishes only once it is committed', function (): void {
    $promptsAtStart = null;

    Event::listen(PromptCreatingActionEvent::class, function () use (&$promptsAtStart): void {
        $promptsAtStart = Prompt::query()->count();
    });

    $finished = recordEvents(PromptCreatedActionEvent::class);

    DB::transaction(function () use ($finished): void {
        app(CreatePromptAction::class)->execute(['name' => 'Support', 'slug' => 'support', 'content' => 'Be kind.']);

        expect($finished)->toHaveCount(0);
    });

    expect($promptsAtStart)->toBe(0)
        ->and($finished)->toHaveCount(1)
        ->and($finished[0]->prompt->slug)->toBe('support');
});

it('starts a failed action but never finishes it', function (): void {
    $prompt = Prompt::factory()->create();
    Agent::factory()->create(['prompt_id' => $prompt->id]);

    $starts = recordEvents(PromptDeletingActionEvent::class);
    $stops = recordEvents(PromptDeletedActionEvent::class);

    expect(fn (): mixed => app(DeletePromptAction::class)->execute($prompt))
        ->toThrow(ValidationException::class);

    expect($starts)->toHaveCount(1)
        ->and($stops)->toHaveCount(0);
});
