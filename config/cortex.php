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
    | JayI\Cortex\Contracts\UiTokenResolver).
    |
    */

    'ui' => [
        'enabled' => true,
        'path' => 'cortex/ui',
        'middleware' => ['web'],
        'auth' => [
            'mode' => 'session',
            'token_resolver' => null,
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
    | Tools
    |--------------------------------------------------------------------------
    |
    | Tools available to agents, keyed by name. Each class must implement
    | Laravel\Ai\Contracts\Tool. Tools may also be registered at runtime
    | via Cortex::tools()->register($name, $class).
    |
    */

    'tools' => [
        // 'search' => \App\Ai\Tools\SearchTool::class,
    ],

];
