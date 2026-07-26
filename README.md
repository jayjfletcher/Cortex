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

AI orchestration for Laravel. Cortex manages **prompts (with immutable versioning), tools, and agents/sub-agents** on top of the [Laravel AI SDK](https://laravel.com/docs/ai-sdk), and exposes the same operations through a REST API and an [MCP](https://laravel.com/docs/mcp) server with full tool parity.

- **Prompts** are versioned: content is immutable per version, and a published pointer decides what agents use. Roll back by publishing an older version.
- **Tools** are PHP classes implementing `Laravel\Ai\Contracts\Tool`, registered by name in the Cortex tool registry.
- **Agents** are database records that combine a prompt (published or pinned version), registered tools, provider/model settings, and other agents as sub-agents. Run them via the API, the MCP server, or the `Cortex` facade.

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
        'auth' => ['mode' => 'session', 'token_resolver' => null],
    ],
    'mcp' => [
        'web' => ['enabled' => false, 'route' => 'mcp/cortex', 'middleware' => []],
        'local' => ['enabled' => false, 'handle' => 'cortex'],
    ],
    'tools' => [
        // 'search' => \App\Ai\Tools\SearchTool::class,
    ],
];
```

> [!WARNING]
> The API routes, dashboard, and MCP server manage **and execute** agents. The MCP transports are disabled by default, the API carries only the `api` middleware group, and the dashboard carries only `web`. Before exposing any of them in production, add authentication — e.g. `'middleware' => ['api', 'auth:sanctum']` for the routes, `'middleware' => ['web', 'auth']` for the dashboard, and `'middleware' => ['auth:sanctum']` for the MCP web transport.

## Dashboard

Cortex ships a prebuilt dashboard (no npm build required in your app) covering prompts and versions, agents, a run playground, and the tool registry. Publish the assets and visit `/cortex/ui`:

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

The dashboard calls the Cortex API from the browser, so it must authenticate the same way the rest of your app does. Two modes:

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

The token is resolved once per page load. For expiring tokens or custom flows (e.g. OAuth refresh), define `window.CortexToken` as an async function before the dashboard script loads — the client will call it for every request and retry once on a 401:

```html
<script>
    window.CortexToken = async (refresh) => await myTokenStore.get({ refresh });
</script>
```

## Registering Tools

Tools implement `Laravel\Ai\Contracts\Tool`. Register them in `config/cortex.php` under `tools`, or at runtime:

```php
use JayI\Cortex\Facades\Cortex;

Cortex::tools()->register('search', \App\Ai\Tools\SearchTool::class);
```

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
| POST | `/cortex/agents/{slug}/run` | Run an agent with `{"input": "..."}` |
| GET | `/cortex/tools` | List registered tools with their schemas |

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

## MCP Server

The `CortexServer` exposes every API operation as an MCP tool (16 tools: prompt CRUD + versions + publish, agent CRUD, list tools, run agent). Enable a transport in the config:

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
