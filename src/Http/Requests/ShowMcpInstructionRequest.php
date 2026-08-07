<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\ShowMcpInstructionAction;
use JayI\Cortex\Http\Resources\McpInstructionResource;

final class ShowMcpInstructionRequest extends McpInstructionRequest
{
    public function persist(): JsonResponse
    {
        $instruction = app(ShowMcpInstructionAction::class)->execute($this->serverName());

        return (new McpInstructionResource($instruction))->response();
    }
}
