<div align="center">
    <h1>Cortex</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/jayi/cortex"><img src="https://img.shields.io/packagist/v/jayi/cortex.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/jayi/cortex"><img src="https://img.shields.io/packagist/php-v/jayi/cortex.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/jayi/cortex"><img src="https://badge.laravel.cloud/badge/jayi/cortex?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/jayi/cortex/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/jayi/cortex/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/jayi/cortex"><img src="https://img.shields.io/packagist/dt/jayi/cortex.svg?style=flat-square" alt="Total Downloads"></a>
</p>

AI orchestration for Laravel. Cortex manages **prompts (with immutable versioning), tools, and agents/sub-agents** on top of the [Laravel AI SDK](https://laravel.com/docs/ai-sdk), exposed through a REST API, a prebuilt dashboard, an [MCP](https://laravel.com/docs/mcp) server mirroring the prompt/agent/tool operations, and a typed TypeScript SDK.

- **Prompts** are versioned: content is immutable per version, and a published pointer decides what agents use. Roll back by publishing an older version.
- **Tools** are PHP classes implementing `Laravel\Ai\Contracts\Tool` or extending `Laravel\Mcp\Server\Tool` (wrapped automatically), registered by name in the Cortex tool registry. Their descriptions can be overridden at runtime with versioned, publishable content.
- **Agents** are database records that combine a prompt (published or pinned version), registered tools, provider/model settings, and other agents as sub-agents. Run them via the API, the dashboard, the MCP server, or the `Cortex` facade.

## Installation

```bash
composer require jayi/cortex
```

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="cortex-migrations"
php artisan migrate
```

Publish the config file to customize routes, the dashboard, MCP transports, and tools:

```bash
php artisan vendor:publish --tag="cortex-config"
```

To use the bundled dashboard, publish its compiled assets:

```bash
php artisan vendor:publish --tag="cortex-assets"
```

## Configuration

```php
return [
    'routes' => [
        'prefix' => 'cortex',
        'middleware' => ['api'],
    ],
    'ui' => [
        'enabled' => true,
        'path' => 'cortex/ui',
        'middleware' => ['web'],
        'auth' => [
            'mode' => 'session', // 'session' | 'token' | 'oauth' | 'custom'
            'token_resolver' => null,
            'oauth' => [
                'client_id' => null,
                'authorize_url' => '/oauth/authorize',
                'token_url' => '/oauth/token',
                'scopes' => [],
            ],
        ],
    ],
    'mcp' => [
        'web' => ['enabled' => false, 'route' => 'mcp/cortex', 'middleware' => []],
        'local' => ['enabled' => false, 'handle' => 'cortex'],
    ],
    'cache' => [
        'enabled' => true,
        'store' => null,
        'fresh' => 300,
        'stale' => 86400,
    ],
    'providers' => [
        // 'anthropic' => ['claude-sonnet-5', 'claude-opus-4-8'],
    ],
    'tools' => [
        // 'search' => \App\Ai\Tools\SearchTool::class,
        // \App\Mcp\Tools\LookupTool::class,
    ],
];
```

> [!WARNING]
> The API routes, dashboard, and MCP server manage **and execute** agents. The MCP transports are disabled by default, the API carries only the `api` middleware group, and the dashboard carries only `web`. Before exposing any of them in production, add authentication — e.g. `'middleware' => ['api', 'auth:sanctum']` for the routes, `'middleware' => ['web', 'auth']` for the dashboard, and `'middleware' => ['auth:sanctum']` for the MCP web transport.

## Dashboard

Cortex ships a prebuilt dashboard (no npm build required in your app) covering prompts and versions, agents, a run playground, the tool registry, and a versioned editor for tool description overrides. Publish the assets and visit `/cortex/ui`:

```bash
php artisan vendor:publish --tag="cortex-assets"
```

Configure it via the `ui` block: `enabled` toggles the route, `path` moves it, and `middleware` gates it. Re-publish assets after package updates:

```bash
php artisan vendor:publish --tag="cortex-assets" --force
```

Or automate that in `composer.json`:

```json
"post-update-cmd": [
    "@php artisan vendor:publish --tag=cortex-assets --force --ansi"
]
```

### Dashboard Authentication

The dashboard calls the Cortex API from the browser, so it must authenticate the same way the rest of your app does. Four modes, selected via `ui.auth.mode`:

**Session mode** (default, `'auth' => ['mode' => 'session']`) — the dashboard sends same-origin cookies plus the CSRF token (`X-XSRF-TOKEN`). Pair it with session middleware on both the dashboard and the API:

```php
'routes' => ['prefix' => 'cortex', 'middleware' => ['web', 'auth']],
'ui' => ['middleware' => ['web', 'auth'], /* ... */],
```

Putting `web` on the API routes enables CSRF verification and session auth for browser calls. If external non-browser consumers also use the API, prefer token mode with `auth:sanctum` instead.

**Token mode** (`'auth' => ['mode' => 'token']`) — the dashboard sends `Authorization: Bearer <token>`, where the token comes from a resolver you register. Works with Sanctum tokens, JWTs, or OAuth access tokens:

```php
'routes' => ['prefix' => 'cortex', 'middleware' => ['api', 'auth:sanctum']],
'ui' => [
    'middleware' => ['web', 'auth'],
    'auth' => [
        'mode' => 'token',
        'token_resolver' => \App\Cortex\DashboardTokenResolver::class,
    ],
],
```

```php
use Illuminate\Http\Request;
use JayI\Cortex\Contracts\UiTokenResolver;

class DashboardTokenResolver implements UiTokenResolver
{
    public function resolve(Request $request): ?string
    {
        return $request->user()?->createToken('cortex-dashboard')->plainTextToken;
    }
}
```

The token is resolved once per page load. For expiring tokens, define `window.CortexToken` as an async function before the dashboard script loads — the client will call it for every request and retry once on a 401:

```html
<script>
    window.CortexToken = async (refresh) => await myTokenStore.get({ refresh });
</script>
```

**OAuth mode** (`'auth' => ['mode' => 'oauth']`) — the dashboard runs an authorization-code + PKCE flow in the browser as a public client (no client secret). Configure the endpoints under `ui.auth.oauth` (works with Passport or any OAuth 2.0 server that supports PKCE):

```php
'routes' => ['prefix' => 'cortex', 'middleware' => ['api', 'auth:api']],
'ui' => [
    'middleware' => ['web', 'auth'],
    'auth' => [
        'mode' => 'oauth',
        'oauth' => [
            'client_id' => env('CORTEX_OAUTH_CLIENT_ID'),
            'authorize_url' => '/oauth/authorize',
            'token_url' => '/oauth/token',
            'scopes' => [],
        ],
    ],
],
```

The dashboard redirects to the authorize URL on first load, exchanges the code on return, keeps tokens in `sessionStorage`, refreshes via `refresh_token` when expiring, and falls back to a fresh authorize redirect if the refresh fails.

**Custom mode** (`'auth' => ['mode' => 'custom']`) — the host page supplies its own auth driver by defining `window.CortexAuth` before the dashboard script loads (publish the views via `cortex-views` to control the shell):

```html
<script>
    window.CortexAuth = {
        async headers(refresh) { return { Authorization: `Bearer ${await getToken(refresh)}` }; },
        async boot() { /* optional: run before the app mounts */ },
        retriesOn401() { return true; }, // optional
    };
</script>
```

## Registering Tools

Tools implement `Laravel\Ai\Contracts\Tool` or extend `Laravel\Mcp\Server\Tool` — MCP tools are wrapped for agent use automatically. Register them in `config/cortex.php` under `tools` (string keys set the registered name; unkeyed entries derive it from the tool itself), or at runtime:

```php
use JayI\Cortex\Facades\Cortex;

Cortex::tools()->register('search', \App\Ai\Tools\SearchTool::class);
```

### Tool Description Overrides

A tool's code-declared description can be overridden without a deploy: each tool has an optional, immutably versioned description with a published pointer — same model as prompts. Manage overrides from the dashboard or the API (`/cortex/tools/{tool}/description`). Extend `JayI\Cortex\Tools\Tool` (or use the `JayI\Cortex\Tools\Concerns\HasVersionedDescription` trait on an existing MCP tool) so the tool also serves its published override when used directly outside Cortex.

## Managing Prompts and Agents

Everything is available over the REST API (prefix `cortex` by default):

| Method | URI | Purpose |
| --- | --- | --- |
| GET/POST | `/cortex/prompts` | List / create prompts (create stores version 1, published by default) |
| GET/PATCH/DELETE | `/cortex/prompts/{slug}` | Show / update metadata / delete |
| GET/POST | `/cortex/prompts/{slug}/versions` | List / create immutable versions |
| GET | `/cortex/prompts/{slug}/versions/{version}` | Show a version |
| POST | `/cortex/prompts/{slug}/versions/{version}/publish` | Publish a version |
| GET/POST | `/cortex/agents` | List / create agents |
| GET/PATCH/DELETE | `/cortex/agents/{slug}` | Show / update / delete |
| POST | `/cortex/agents/{slug}/run` | Run an agent with `{"input": "..."}` — returns `{text, usage}` |
| GET | `/cortex/providers` | List providers with their models and default model |
| GET | `/cortex/tools` | List registered tools with their schemas |
| GET/DELETE | `/cortex/tools/{tool}/description` | Show / remove the description override |
| GET/POST | `/cortex/tools/{tool}/description/versions` | List / create immutable override versions |
| POST | `/cortex/tools/{tool}/description/versions/{version}/publish` | Publish an override version |

Agent create/update payloads accept `tools` (registered tool names), `prompt` (prompt slug), `prompt_version` (pin a version; omit to follow the published version), and `sub_agents` (agent slugs). The `tools` and `sub_agents` lists use sync semantics — send the desired end state. Circular sub-agent references are rejected.

```json
{
    "name": "Coordinator",
    "slug": "coordinator",
    "provider": "anthropic",
    "model": "claude-sonnet-5",
    "settings": {"temperature": 0.3, "max_steps": 10},
    "tools": ["search"],
    "prompt": "support",
    "sub_agents": ["researcher"]
}
```

## Running Agents from Code

```php
use JayI\Cortex\Facades\Cortex;

$response = Cortex::run('coordinator', 'Summarize the open tickets.');

$response->text;

// Or build the laravel/ai agent yourself:
Cortex::agent('coordinator')->stream('...');
```

Providers, models, and settings fall back to your app's `config/ai.php` defaults when not set on the agent.

## Providers

The dashboard's agent form offers providers and models from `GET /cortex/providers`. By default every text-capable provider configured for laravel/ai is offered, along with the models it declares (default, smartest, cheapest). Set `cortex.providers` to curate the list — it becomes authoritative when non-empty, with the first model of each provider used as its default:

```php
'providers' => [
    'anthropic' => ['claude-sonnet-5', 'claude-opus-4-8'],
],
```

## Publication Cache

Published prompt content and tool description overrides are cached so agent runs and tool listings don't hit the database on every request; publishing invalidates explicitly. When Redis is available it is preferred and read via `Cache::flexible()` using the `cache.fresh`/`cache.stale` windows (stale-while-revalidate); any other store caches until invalidation. Pin a store with `cache.store`, or set `cache.enabled` to `false` to read from the database on every pull.

## MCP Server

The `CortexServer` exposes the prompt, agent, and tool operations as MCP tools (16 tools: prompt CRUD + versions + publish, agent CRUD, list tools, run agent). The provider and tool-description endpoints are HTTP-only. Enable a transport in the config:

```php
'mcp' => [
    'web' => ['enabled' => true, 'route' => 'mcp/cortex', 'middleware' => ['auth:sanctum']],
    'local' => ['enabled' => true, 'handle' => 'cortex'],
],
```

The web transport serves streamable HTTP at `/mcp/cortex`; the local transport is started with `php artisan mcp:start cortex` and inspectable with `php artisan mcp:inspector cortex`. Alternatively, keep both disabled and register the server yourself in `routes/ai.php`:

```php
use JayI\Cortex\Mcp\CortexServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/cortex', CortexServer::class)->middleware(['auth:sanctum']);
```

## TypeScript SDK

The dashboard consumes `@jayi/cortex-sdk` (in `sdk/`), a type-safe client generated from the package's OpenAPI spec with [openapi-fetch](https://openapi-ts.dev/openapi-fetch/). Use it in your own frontend:

```ts
import { createCortexClient } from '@jayi/cortex-sdk';

const cortex = createCortexClient({
    baseUrl: 'https://example.test', // origin only — spec paths include the /cortex prefix
    accessToken: token,              // optional; or pass a custom fetch for dynamic auth
});

const { data, error } = await cortex.GET('/cortex/agents');
```

The spec bakes in the default `cortex` route prefix — regenerate it (`npm run sdk:generate` in the package repo) if you change `routes.prefix`.

## Testing Your Integration

Fake agent responses with the Laravel AI SDK's testing helpers — Cortex agents all run through `JayI\Cortex\Runtime\DbAgent`:

```php
use JayI\Cortex\Runtime\DbAgent;

DbAgent::fake(['Canned response.']);

DbAgent::assertPrompted(fn ($prompt) => str_contains($prompt->prompt, 'tickets'));
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Cortex! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Jay Fletcher](https://github.com/jayi)
- [All Contributors](../../contributors)

## License

Cortex is open-sourced software licensed under the [MIT license](LICENSE.md).
