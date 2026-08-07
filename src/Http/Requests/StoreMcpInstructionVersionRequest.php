<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\CreateMcpInstructionVersionAction;
use JayI\Cortex\Http\Resources\McpInstructionVersionResource;

final class StoreMcpInstructionVersionRequest extends McpInstructionRequest
{
    public function rules(): array
    {
        return CreateMcpInstructionVersionAction::rules();
    }

    public function persist(): JsonResponse
    {
        $version = app(CreateMcpInstructionVersionAction::class)->execute($this->serverName(), $this->validated());

        return (new McpInstructionVersionResource($version))->response()->setStatusCode(201);
    }
}
