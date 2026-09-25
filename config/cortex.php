<?php

declare(strict_types=1);

use JayI\Cortex\Models\Agent;
use JayI\Cortex\Models\McpInstruction;
use JayI\Cortex\Models\McpInstructionVersion;
use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Models\PromptVersion;
use JayI\Cortex\Models\ToolDescription;
use JayI\Cortex\Models\ToolDescriptionVersion;
use JayI\Cortex\Policies\AgentPolicy;
use JayI\Cortex\Policies\McpInstructionPolicy;
use JayI\Cortex\Policies\McpInstructionVersionPolicy;
use JayI\Cortex\Policies\PromptPolicy;
use JayI\Cortex\Policies\PromptVersionPolicy;
use JayI\Cortex\Policies\ToolDescriptionPolicy;
use JayI\Cortex\Policies\ToolDescriptionVersionPolicy;

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
    | Policies
    |--------------------------------------------------------------------------
    |
    | The policy the Gate uses for each model. The JSON API and MCP tools
    | check every call against these, as the authenticated user (or as a
    | guest when nobody is signed in). Cortex records have no owner, so the
    | bundled policies allow everything and your route middleware stays the
    | gate, as before. Versions defer to their prompt or override: reading
    | one needs `view` on it, adding or publishing one needs `update`.
    | Point a model at your own class to replace its policy.
    |
    */

    'policies' => [
        Agent::class => AgentPolicy::class,
        Prompt::class => PromptPolicy::class,
        PromptVersion::class => PromptVersionPolicy::class,
        ToolDescription::class => ToolDescriptionPolicy::class,
        ToolDescriptionVersion::class => ToolDescriptionVersionPolicy::class,
        McpInstruction::class => McpInstructionPolicy::class,
        McpInstructionVersion::class => McpInstructionVersionPolicy::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard UI
    |--------------------------------------------------------------------------
    |
    | Cortex renders its dashboard through Atrium, which owns the path,
    | middleware and authorization gate. Set this to false to keep the JSON
    | API without adding Cortex to the dashboard.
    |
    */

    'ui' => [

        /*
        | Whether Cortex registers itself with the Atrium dashboard. The JSON
        | API is unaffected by this switch.
        */

        'enabled' => true,
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
    | The `servers` list holds MCP server classes whose instructions Cortex
    | manages (versioned overrides replacing the code-declared value when
    | published). Cortex's own server is always registered as 'cortex'.
    | String keys set the server's registered name; unkeyed entries
    | derive it from the class. Servers may also be registered at
    | runtime via Cortex::servers()->register($name, $class).
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
        'servers' => [
            // 'support' => \App\Mcp\SupportServer::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Publication Cache
    |--------------------------------------------------------------------------
    |
    | Published prompt versions, tool description overrides, and MCP server
    | instruction overrides are cached so agent runs and MCP listings don't
    | hit the database on every request; the publishing actions invalidate
    | explicitly. When Redis is
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
