<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\ListMcpInstructionVersionsAction;
use JayI\Cortex\Http\Resources\McpInstructionVersionResource;

final class IndexMcpInstructionVersionsRequest extends McpInstructionRequest
{
    public function persist(): JsonResponse
    {
        $versions = app(ListMcpInstructionVersionsAction::class)->execute($this->instruction());

        return McpInstructionVersionResource::collection($versions)->response();
    }
}
