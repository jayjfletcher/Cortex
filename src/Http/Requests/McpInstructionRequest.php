<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use JayI\Cortex\Http\Request;
use JayI\Cortex\Mcp\McpServerRegistry;
use JayI\Cortex\Models\McpInstruction;

abstract class McpInstructionRequest extends Request
{
    /**
     * The registered server name from the route, verified against the
     * registry. Named serverName() because Illuminate\Http\Request
     * already declares a public server() for the server params.
     */
    protected function serverName(): string
    {
        $server = $this->route('server');

        if (! is_string($server) || ! app(McpServerRegistry::class)->has($server)) {
            abort(404);
        }

        return $server;
    }

    protected function instruction(): McpInstruction
    {
        $instruction = McpInstruction::query()->where('server', $this->serverName())->first();

        if ($instruction === null) {
            abort(404);
        }

        return $instruction;
    }
}
