---
name: cortex-development
description: >
  Configure and apply the Cortex package in Laravel applications: versioned
  prompts, a tool registry with versioned description overrides, an MCP
  server registry with versioned instruction overrides, and DB-backed
  AI agents exposed through a REST API, a prebuilt dashboard, and an
  MCP server.
license: MIT
metadata:
  author: Jay Fletcher
---

# Cortex

Use this skill when a Laravel application needs to integrate the `jayi/cortex` package to manage prompts (immutable versions + published pointer), register AI tools, and configure or run agents/sub-agents built on the Laravel AI SDK.

## Primary Goal

- apply the `jayi/cortex` package's public API in the smallest correct way

## Workflow

### 1. Install and migrate

```bash
composer require jayi/cortex
php artisan vendor:publish --tag="cortex-migrations"
php artisan migrate
```

Publish config only when route prefix, middleware, the dashboard, MCP transports, or config-registered tools must change:

```bash
php artisan vendor:publish --tag="cortex-config"
```

### 2. Secure the surface before enabling it

The API routes, dashboard, and MCP server manage **and execute** agents. MCP transports are disabled by default and the routes ship on `api` middleware only. Always add auth middleware to the routes and the MCP web transport before production exposure. The dashboard is guarded by Atrium's `viewAtrium` gate (see step 3):

```php
// config/cortex.php
'routes' => ['prefix' => 'cortex', 'middleware' => ['api', 'auth:sanctum']],
'mcp' => [
    'web' => ['enabled' => true, 'route' => 'mcp/cortex', 'middleware' => ['auth:sanctum']],
    'local' => ['enabled' => true, 'handle' => 'cortex'],   // php artisan mcp:start cortex
],
```

Every API endpoint and MCP tool that touches a model is also checked through the Gate against `cortex.policies`, as the signed-in user or as a guest. Cortex records have no owner, so the bundled policies allow everything and the middleware stays the gate. To restrict something, extend the bundled policy (e.g. `JayI\Cortex\Policies\PromptPolicy`), override the ability (`update`, `delete`, `publish`, `run`, ...) and point the model at it in `cortex.policies`. Version policies defer to their prompt or override: `view` to read, `update` to add or publish. Type the user parameter as non-nullable to refuse guests.

### 3. Enable the dashboard (optional)

Cortex renders a server-side Blade dashboard (prompts, agents, run playground, tools, tool description overrides, MCP server instruction overrides) through the `jayi/atrium` package, which it requires. The pages live under Atrium's path (`/atrium/cortex/...` by default) and use Atrium's middleware and gate, so they authenticate like the rest of the app. Publish Atrium's assets and define its gate:

```bash
php artisan vendor:publish --tag="atrium-assets"
```

```php
// e.g. in AppServiceProvider::boot()
Gate::define('viewAtrium', fn ($user) => $user->is_admin);
```

Without a `viewAtrium` gate Atrium allows the `local` environment only. Set `'ui' => ['enabled' => false]` to leave Cortex out of the dashboard; the JSON API keeps serving.

### 4. Register tools

Tools implement `Laravel\Ai\Contracts\Tool` (`description()`, `handle()`, `schema()`) or extend `Laravel\Mcp\Server\Tool` (wrapped for agent use automatically). String config keys set the registered name; unkeyed entries derive it from the tool:

```php
// config/cortex.php
'tools' => [
    'search' => \App\Ai\Tools\SearchTool::class,
    \App\Mcp\Tools\LookupTool::class,
],

// or at runtime (e.g. in a service provider):
use JayI\Cortex\Facades\Cortex;
Cortex::tools()->register('search', \App\Ai\Tools\SearchTool::class);
```

To let a tool's description be overridden at runtime (versioned + published like prompts), extend `JayI\Cortex\Tools\Tool` or use the `JayI\Cortex\Tools\Concerns\HasVersionedDescription` trait. Manage overrides from the dashboard or `/cortex/tools/{tool}/description` endpoints.

MCP server *instructions* work the same way. Cortex's own server is always registered as `cortex`; register app servers under `cortex.mcp.servers` config (string keys name them; unkeyed entries derive the name from `#[Name]` or the class basename) or at runtime with `Cortex::servers()->register('support', \App\Mcp\SupportServer::class)`. For published overrides to be served to MCP clients, the server must extend `JayI\Cortex\Mcp\Server` or use the `JayI\Cortex\Mcp\Concerns\HasVersionedInstructions` trait.

### 5. Manage prompts and agents via the API (or MCP tools)

- `POST /cortex/prompts` `{name, slug, content, publish?}` — creates version 1, published by default.
- `POST /cortex/prompts/{slug}/versions` `{content, publish?}` — content changes always create a new immutable version.
- `POST /cortex/prompts/{slug}/versions/{version}/publish` — move the published pointer (rollback = publish an older version).
- `POST /cortex/agents` — `{name, slug, provider?, model?, settings?, tools?, prompt?, prompt_version?, sub_agents?}`. `tools` are registered tool names; `prompt` is a prompt slug; omit `prompt_version` to follow the published version; `sub_agents` are agent slugs. `tools`/`sub_agents` use sync semantics (send the full desired list). Circular sub-agent references are rejected.
- `POST /cortex/agents/{slug}/run` `{input}` — execute; returns `{text, usage}`.
- `GET /cortex/providers` — providers with models and default model. Curate via `cortex.providers` config (authoritative when non-empty); otherwise every text-capable laravel/ai provider is offered.
- `GET /cortex/tools` — registered tools with JSON schemas.
- `GET|DELETE /cortex/tools/{tool}/description`, `GET|POST .../description/versions`, `POST .../versions/{version}/publish` — versioned tool description overrides.
- `GET /cortex/servers` — registered MCP servers with their effective instructions.
- `GET|DELETE /cortex/servers/{server}/instructions`, `GET|POST .../instructions/versions`, `POST .../versions/{version}/publish` — versioned server instruction overrides (rollback = publish an older version).

The MCP server (`JayI\Cortex\Mcp\CortexServer`) exposes the prompt, agent, tool-list, and server-instruction operations as 22 MCP tools; the provider and tool-description endpoints are HTTP-only.

Published prompt content, description overrides, and server instruction overrides are cached (`cortex.cache` config: Redis preferred with stale-while-revalidate, other stores cache until publish invalidates; disable with `'cache' => ['enabled' => false]`).

### 6. Run agents from code

```php
use JayI\Cortex\Facades\Cortex;

$response = Cortex::run('coordinator', 'Summarize the open tickets.');
$response->text;

Cortex::agent('coordinator')->stream('...'); // full laravel/ai agent
```

Provider/model/settings fall back to the app's `config/ai.php` defaults when unset on the agent.

### 7. React to changes

- Model events: one class per Eloquent hook per model, e.g. `JayI\Cortex\Events\Model\PromptVersionCreatedEvent` (`$event->version`).
- Action events: a start and a finish event per action, e.g. `PromptVersionPublishingActionEvent` then `PromptVersionPublishedActionEvent` (`$event->prompt`), or `AgentRunningActionEvent` then `AgentRanActionEvent` (`$event->agent`, `$event->input`, `$event->response`). Finish events fire after commit and only on success.
- Listen to `JayI\Cortex\Contracts\ActionFinishedEvent` or `ModelLifecycleEvent` to see a whole family. In tests, fake only the events you assert on: `Event::fake([PromptVersionPublishedActionEvent::class])`.

### 8. Test the integration

```php
use JayI\Cortex\Runtime\DbAgent;

DbAgent::fake(['Canned response.']);
// ...run the agent via API, MCP, or Cortex::run()...
DbAgent::assertPrompted(fn ($prompt) => str_contains($prompt->prompt, 'tickets'));
```

Always fake — unfaked runs require a configured AI provider.

## Rules, References, and Templates

Read before executing:

- `config/cortex.php` — routes, dashboard switch, MCP transports, cache, providers, tools, policies
- `docs/policies.md` — which ability each endpoint and MCP tool checks
- `docs/events.md` — every action with its start and finish events
- `README.md` — full route table and payload examples

## Examples

- Wire a support agent: create prompt `support` with instructions, register a `search` tool, `POST /cortex/agents` with `{"slug": "support-agent", "prompt": "support", "tools": ["search"]}`, then `Cortex::run('support-agent', $question)`.
- Safe prompt iteration: `POST /cortex/prompts/support/versions` with new content (unpublished), verify, then publish it; pinned agents (`prompt_version` set) are unaffected until re-pinned.

## Anti-patterns

- do not document package internals here; keep the skill focused on adoption in Laravel apps
- do not edit prompt version content — versions are immutable; create a new version and publish it (tool description and server instruction override versions work the same way)
- do not enable the MCP web transport or expose the routes without auth middleware, or ship the dashboard without a `viewAtrium` gate
- do not attach tool class names to agents — attach the registered tool *names* from the registry
