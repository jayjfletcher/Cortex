<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use JayI\Cortex\Mcp\McpServerRegistry;
use JayI\Cortex\Mcp\Request;
use JayI\Cortex\Models\McpInstruction;
use JayI\Cortex\Models\McpInstructionVersion;

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

    /**
     * The version named in the input.
     */
    protected function version(): McpInstructionVersion
    {
        /** @var McpInstructionVersion */
        return $this->instruction()->versions()->where('version', (int) $this->get('version'))->firstOrFail();
    }

    /**
     * The override for the server, or an unsaved one when no version exists yet, so
     * creating the first version is checked against the same policy.
     */
    protected function instructionOrNew(): McpInstruction
    {
        return McpInstruction::query()->firstOrNew(['server' => $this->serverName()]);
    }
}
