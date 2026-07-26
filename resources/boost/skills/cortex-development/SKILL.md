---
name: cortex-development
description: >
  Configure and apply the Cortex package in Laravel applications: versioned
  prompts, a tool registry, and DB-backed AI agents exposed through a REST
  API, a prebuilt dashboard, and an MCP server with tool parity.
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

The API routes, dashboard, and MCP server manage **and execute** agents. MCP transports are disabled by default; the routes ship on `api` middleware only and the dashboard on `web` only. Always add auth middleware before production exposure:

```php
// config/cortex.php
'routes' => ['prefix' => 'cortex', 'middleware' => ['api', 'auth:sanctum']],
'ui' => ['middleware' => ['web', 'auth'], /* ... */],
'mcp' => [
    'web' => ['enabled' => true, 'route' => 'mcp/cortex', 'middleware' => ['auth:sanctum']],
    'local' => ['enabled' => true, 'handle' => 'cortex'],   // php artisan mcp:start cortex
],
```

### 3. Enable the dashboard (optional)

A prebuilt dashboard (prompts, agents, run playground, tools) mounts at `/cortex/ui`. Publish its compiled assets — no npm build in the app:

```bash
php artisan vendor:publish --tag="cortex-assets"          # re-run with --force after package updates
```

The dashboard authenticates its API calls via `ui.auth.mode`:

- `session` (default): sends cookies + CSRF. Pair `'routes' => ['middleware' => ['web', 'auth']]` with `'ui' => ['middleware' => ['web', 'auth']]`.
- `token`: sends `Authorization: Bearer` from a resolver implementing `JayI\Cortex\Contracts\UiTokenResolver` (works with Sanctum/JWT/OAuth tokens):

```php
'ui' => [
    'middleware' => ['web', 'auth'],
    'auth' => ['mode' => 'token', 'token_resolver' => \App\Cortex\DashboardTokenResolver::class],
],
```

Set `'ui' => ['enabled' => false]` to remove the dashboard route entirely.

### 4. Register tools

Tools are classes implementing `Laravel\Ai\Contracts\Tool` (`description()`, `handle()`, `schema()`), registered by name:

```php
// config/cortex.php
'tools' => ['search' => \App\Ai\Tools\SearchTool::class],

// or at runtime (e.g. in a service provider):
use JayI\Cortex\Facades\Cortex;
Cortex::tools()->register('search', \App\Ai\Tools\SearchTool::class);
```

### 5. Manage prompts and agents via the API (or MCP tools)

- `POST /cortex/prompts` `{name, slug, content, publish?}` — creates version 1, published by default.
- `POST /cortex/prompts/{slug}/versions` `{content, publish?}` — content changes always create a new immutable version.
- `POST /cortex/prompts/{slug}/versions/{version}/publish` — move the published pointer (rollback = publish an older version).
- `POST /cortex/agents` — `{name, slug, provider?, model?, settings?, tools?, prompt?, prompt_version?, sub_agents?}`. `tools` are registered tool names; `prompt` is a prompt slug; omit `prompt_version` to follow the published version; `sub_agents` are agent slugs. `tools`/`sub_agents` use sync semantics (send the full desired list). Circular sub-agent references are rejected.
- `POST /cortex/agents/{slug}/run` `{input}` — execute; returns `{text, usage}`.
- `GET /cortex/tools` — registered tools with JSON schemas.

The MCP server (`JayI\Cortex\Mcp\CortexServer`) exposes the same 16 operations as MCP tools.

### 6. Run agents from code

```php
use JayI\Cortex\Facades\Cortex;

$response = Cortex::run('coordinator', 'Summarize the open tickets.');
$response->text;

Cortex::agent('coordinator')->stream('...'); // full laravel/ai agent
```

Provider/model/settings fall back to the app's `config/ai.php` defaults when unset on the agent.

### 7. Test the integration

```php
use JayI\Cortex\Runtime\DbAgent;

DbAgent::fake(['Canned response.']);
// ...run the agent via API, MCP, or Cortex::run()...
DbAgent::assertPrompted(fn ($prompt) => str_contains($prompt->prompt, 'tickets'));
```

Always fake — unfaked runs require a configured AI provider.

## Rules, References, and Templates

Read before executing:

- `config/cortex.php` — routes, dashboard, MCP transports, tools
- `README.md` — full route table and payload examples

## Examples

- Wire a support agent: create prompt `support` with instructions, register a `search` tool, `POST /cortex/agents` with `{"slug": "support-agent", "prompt": "support", "tools": ["search"]}`, then `Cortex::run('support-agent', $question)`.
- Safe prompt iteration: `POST /cortex/prompts/support/versions` with new content (unpublished), verify, then publish it; pinned agents (`prompt_version` set) are unaffected until re-pinned.

## Anti-patterns

- do not document package internals here; keep the skill focused on adoption in Laravel apps
- do not edit prompt version content — versions are immutable; create a new version and publish it
- do not enable the MCP web transport or expose the routes or dashboard without auth middleware
- do not rebuild dashboard assets in the consuming app — publish the compiled bundle with `--tag="cortex-assets"`
- do not attach tool class names to agents — attach the registered tool *names* from the registry
