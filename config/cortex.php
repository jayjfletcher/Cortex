<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | HTTP API Routes
    |--------------------------------------------------------------------------
    |
    | The prefix and middleware applied to the Cortex management API routes.
    | Add authentication middleware (e.g. auth:sanctum) before exposing
    | these routes in production - they manage and execute agents.
    |
    */

    'routes' => [
        'prefix' => 'cortex',
        'middleware' => ['api'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard UI
    |--------------------------------------------------------------------------
    |
    | Cortex ships a prebuilt dashboard mounted at the configured path. Add
    | authentication middleware (e.g. ['web', 'auth']) before exposing it
    | in production. The auth mode controls how the dashboard talks to
    | the API: 'session' sends same-origin cookies plus the CSRF
    | token, 'token' sends a bearer token returned by the
    | configured resolver (a class implementing
    | JayI\Cortex\Contracts\UiTokenResolver),
    | 'oauth' runs an authorization-code + PKCE flow in the browser
    | against the endpoints configured below (a public client), and
    | 'custom' bridges to a window.CortexAuth driver the host page
    | defines before the dashboard script loads.
    |
    */

    'ui' => [
        'enabled' => true,
        'path' => 'cortex/ui',
        'middleware' => ['web'],
        'auth' => [
            'mode' => 'session',
            'token_resolver' => null,
            'oauth' => [
                'client_id' => null,
                'authorize_url' => '/oauth/authorize',
                'token_url' => '/oauth/token',
                'scopes' => [],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | MCP Servers
    |--------------------------------------------------------------------------
    |
    | Cortex can register its MCP server for you. Both transports are
    | disabled by default. When enabling the web transport, add auth
    | middleware - the server manages and executes agents.
    |
    */

    'mcp' => [
        'web' => [
            'enabled' => false,
            'route' => 'mcp/cortex',
            'middleware' => [],
        ],
        'local' => [
            'enabled' => false,
            'handle' => 'cortex',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Publication Cache
    |--------------------------------------------------------------------------
    |
    | Published prompt versions and tool description overrides are cached so
    | agent runs and MCP listings don't hit the database on every request;
    | the publishing actions invalidate explicitly. When Redis is
    | available it is preferred and read via Cache::flexible()
    | (fresh/stale seconds below); any other store caches
    | until invalidation. Set `store` to pin a store, or
    | `enabled` to false to read straight from the
    | database on every pull.
    |
    */

    'cache' => [
        'enabled' => true,
        'store' => null,
        'fresh' => 300,
        'stale' => 86400,
    ],

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    |
    | The providers (and models) offered when configuring an agent, keyed by
    | provider name with a list of model names. Leave empty to offer every
    | text-capable provider configured for laravel/ai along with the
    | models it declares (default, smartest, cheapest).
    |
    */

    'providers' => [
        // 'anthropic' => ['claude-sonnet-5', 'claude-opus-4-8'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tools
    |--------------------------------------------------------------------------
    |
    | Tools available to agents. Each class must implement
    | Laravel\Ai\Contracts\Tool or extend Laravel\Mcp\Server\Tool (MCP tools
    | are wrapped for agent use automatically). String keys set the tool's
    | registered name; unkeyed entries derive it from the tool itself.
    | Tools may also be registered at runtime via
    | Cortex::tools()->register($name, $class).
    |
    */

    'tools' => [
        // 'search' => \App\Ai\Tools\SearchTool::class,
        // \App\Mcp\Tools\LookupTool::class,
    ],

];
