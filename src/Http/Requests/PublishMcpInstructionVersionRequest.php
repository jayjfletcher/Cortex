<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\PublishMcpInstructionVersionAction;
use JayI\Cortex\Http\Resources\McpInstructionResource;

final class PublishMcpInstructionVersionRequest extends McpInstructionRequest
{
    public function persist(): JsonResponse
    {
        $instruction = app(PublishMcpInstructionVersionAction::class)->execute(
            $this->instruction(),
            (int) $this->route('version'),
        );

        return (new McpInstructionResource($instruction))->response();
    }
}
