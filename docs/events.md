# Events

Cortex fires two families of events:

- **Model events:** every Eloquent lifecycle hook of every Cortex model, one class per hook.
- **Action events:** a start event and a finish event for every action. The JSON API and the MCP tools both run through the actions, and so does your code when it resolves one from the container.

Every event carries the models involved, not just their ids. Every event also uses `Dispatchable` and `SerializesModels`, so it can be dispatched with `::dispatch()` and handled by queued listeners.

## Model events

Each model fires a class-based event for the 10 hooks that apply to models without soft deletes: `retrieved`, `creating`, `created`, `updating`, `updated`, `saving`, `saved`, `deleting`, `deleted` and `replicating`. No Cortex model uses soft deletes, so there are no `restoring`, `restored`, `trashed`, `forceDeleting` or `forceDeleted` events.

They live in `JayI\Cortex\Events\Model` and are named `{Model}{Hook}Event`, for example `PromptCreatingEvent` or `AgentDeletedEvent`. The model is a typed property:

| Model | Property |
| --- | --- |
| `Agent` | `$event->agent` |
| `Prompt` | `$event->prompt` |
| `PromptVersion` | `$event->version` |
| `ToolDescription` | `$event->description` |
| `ToolDescriptionVersion` | `$event->version` |
| `McpInstruction` | `$event->instruction` |
| `McpInstructionVersion` | `$event->version` |

The model is also available as `$event->model()`, alongside `$event->hook()`.

```php
use JayI\Cortex\Events\Model\PromptVersionCreatedEvent;

Event::listen(PromptVersionCreatedEvent::class, function (PromptVersionCreatedEvent $event) {
    Log::info('New prompt version', ['prompt' => $event->version->prompt_id, 'version' => $event->version->version]);
});
```

- **Synchronous:** they fire when Eloquent fires the hook, as Eloquent's own events do.
- **Cancelling:** a `creating`, `updating`, `saving` or `deleting` listener that returns `false` stops the operation.
- **Your own mapping:** entries a model declares on `$dispatchesEvents` win over the derived ones.

The mapping is done by the `DispatchesModelEvents` trait (`JayI\Cortex\Models\Concerns`).

## Action events

Every action dispatches two events:

1. **A start event** (`…ingActionEvent`, e.g. `PromptVersionPublishingActionEvent`), before the action does any work. It carries the action's input.
2. **A finish event** (`…edActionEvent`, e.g. `PromptVersionPublishedActionEvent`), once the action has succeeded. It carries the result.

```php
use JayI\Cortex\Events\Action\AgentRanActionEvent;
use JayI\Cortex\Events\Action\PromptVersionPublishedActionEvent;

Event::listen(PromptVersionPublishedActionEvent::class, function (PromptVersionPublishedActionEvent $event) {
    Notification::route('slack', config('services.slack.prompts'))
        ->notify(new PromptPublished($event->prompt));
});

Event::listen(AgentRanActionEvent::class, function (AgentRanActionEvent $event) {
    Metrics::record($event->agent->slug, $event->response->usage);
});
```

- **Failure:** an action that throws fires its start event and no finish event. Deleting a prompt that agents still use fires `PromptDeletingActionEvent` only.
- **Timing:** finish events implement `ShouldDispatchAfterCommit`, so inside a transaction they fire once it commits and never for work that was rolled back. Start events fire immediately.

Action events live in `JayI\Cortex\Events\Action`.

## Listening to a whole family

Each family implements an interface in `JayI\Cortex\Contracts`, and Laravel delivers an event to listeners of the interfaces it implements:

| Interface | Receives |
| --- | --- |
| `ModelLifecycleEvent` | every model event |
| `ActionStartingEvent` | every action start event |
| `ActionFinishedEvent` | every action finish event |

```php
use JayI\Cortex\Contracts\ActionFinishedEvent;

Event::listen(ActionFinishedEvent::class, fn (ActionFinishedEvent $event) => AuditLog::record($event));
```

## Every action and its events

| Action | Start event | Carries | Finish event | Carries |
| --- | --- | --- | --- | --- |
| `CreateAgentAction` | `AgentCreatingActionEvent` | `$data` | `AgentCreatedActionEvent` | `$agent` |
| `CreateMcpInstructionVersionAction` | `McpInstructionVersionCreatingActionEvent` | `$server`, `$data` | `McpInstructionVersionCreatedActionEvent` | `$server`, `$version` |
| `CreatePromptAction` | `PromptCreatingActionEvent` | `$data` | `PromptCreatedActionEvent` | `$prompt` |
| `CreatePromptVersionAction` | `PromptVersionCreatingActionEvent` | `$prompt`, `$data` | `PromptVersionCreatedActionEvent` | `$prompt`, `$version` |
| `CreateToolDescriptionVersionAction` | `ToolDescriptionVersionCreatingActionEvent` | `$tool`, `$data` | `ToolDescriptionVersionCreatedActionEvent` | `$tool`, `$version` |
| `DeleteAgentAction` | `AgentDeletingActionEvent` | `$agent` | `AgentDeletedActionEvent` | `$agent` |
| `DeleteMcpInstructionAction` | `McpInstructionDeletingActionEvent` | `$instruction` | `McpInstructionDeletedActionEvent` | `$instruction` |
| `DeletePromptAction` | `PromptDeletingActionEvent` | `$prompt` | `PromptDeletedActionEvent` | `$prompt` |
| `DeleteToolDescriptionAction` | `ToolDescriptionDeletingActionEvent` | `$description` | `ToolDescriptionDeletedActionEvent` | `$description` |
| `ListAgentsAction` | `AgentsListingActionEvent` | `$page` | `AgentsListedActionEvent` | `$agents` |
| `ListMcpInstructionVersionsAction` | `McpInstructionVersionsListingActionEvent` | `$instruction` | `McpInstructionVersionsListedActionEvent` | `$instruction`, `$versions` |
| `ListMcpServersAction` | `McpServersListingActionEvent` | — | `McpServersListedActionEvent` | `$servers` |
| `ListPromptVersionsAction` | `PromptVersionsListingActionEvent` | `$prompt`, `$page` | `PromptVersionsListedActionEvent` | `$prompt`, `$versions` |
| `ListPromptsAction` | `PromptsListingActionEvent` | `$page` | `PromptsListedActionEvent` | `$prompts` |
| `ListProvidersAction` | `ProvidersListingActionEvent` | — | `ProvidersListedActionEvent` | `$providers` |
| `ListToolDescriptionVersionsAction` | `ToolDescriptionVersionsListingActionEvent` | `$description` | `ToolDescriptionVersionsListedActionEvent` | `$description`, `$versions` |
| `ListToolsAction` | `ToolsListingActionEvent` | — | `ToolsListedActionEvent` | `$tools` |
| `PublishMcpInstructionVersionAction` | `McpInstructionVersionPublishingActionEvent` | `$instruction`, `$version` | `McpInstructionVersionPublishedActionEvent` | `$instruction` |
| `PublishPromptVersionAction` | `PromptVersionPublishingActionEvent` | `$prompt`, `$version` | `PromptVersionPublishedActionEvent` | `$prompt` |
| `PublishToolDescriptionVersionAction` | `ToolDescriptionVersionPublishingActionEvent` | `$description`, `$version` | `ToolDescriptionVersionPublishedActionEvent` | `$description` |
| `RunAgentAction` | `AgentRunningActionEvent` | `$agent`, `$input` | `AgentRanActionEvent` | `$agent`, `$input`, `$response` |
| `ShowAgentAction` | `AgentShowingActionEvent` | `$agent` | `AgentShownActionEvent` | `$agent` |
| `ShowMcpInstructionAction` | `McpInstructionShowingActionEvent` | `$server` | `McpInstructionShownActionEvent` | `$instruction` |
| `ShowPromptAction` | `PromptShowingActionEvent` | `$prompt` | `PromptShownActionEvent` | `$prompt` |
| `ShowPromptVersionAction` | `PromptVersionShowingActionEvent` | `$prompt`, `$version` | `PromptVersionShownActionEvent` | `$prompt`, `$version` |
| `ShowToolDescriptionAction` | `ToolDescriptionShowingActionEvent` | `$tool` | `ToolDescriptionShownActionEvent` | `$description` |
| `UpdateAgentAction` | `AgentUpdatingActionEvent` | `$agent`, `$data` | `AgentUpdatedActionEvent` | `$agent` |
| `UpdatePromptAction` | `PromptUpdatingActionEvent` | `$prompt`, `$data` | `PromptUpdatedActionEvent` | `$prompt` |

`$data` is the validated input array. `$page` is the requested page number, or `null`. On the list actions for tools, servers and providers, the finish event carries the listed rows as arrays.

## Testing

Fake only the events you assert on, so the rest of Cortex keeps working:

```php
use JayI\Cortex\Events\Action\PromptVersionPublishedActionEvent;

Event::fake([PromptVersionPublishedActionEvent::class]);

$this->postJson('/cortex/prompts/support/versions/2/publish')->assertOk();

Event::assertDispatched(PromptVersionPublishedActionEvent::class, fn ($event) => $event->prompt->slug === 'support');
```
