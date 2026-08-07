<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use JayI\Cortex\Mcp\McpServerRegistry;
use JayI\Cortex\Mcp\Request;
use JayI\Cortex\Models\McpInstruction;

abstract class ServerMcpRequest extends Request
{
    private ?McpInstruction $instruction = null;

    /**
     * The registered server name from the tool input, verified against the
     * registry. Unknown names surface as the base request's not-found error.
     */
    protected function serverName(): string
    {
        $server = (string) $this->get('server');

        if (! app(McpServerRegistry::class)->has($server)) {
            throw (new ModelNotFoundException)->setModel(McpInstruction::class);
        }

        return $server;
    }

    protected function instruction(): McpInstruction
    {
        return $this->instruction ??= McpInstruction::query()
            ->where('server', $this->serverName())
            ->firstOrFail();
    }
}
