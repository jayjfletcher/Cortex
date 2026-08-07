<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use JayI\Cortex\Actions\ListMcpInstructionVersionsAction;
use JayI\Cortex\Http\Resources\McpInstructionVersionResource;
use Laravel\Mcp\ResponseFactory;

final class ListServerInstructionVersionsMcpRequest extends ServerMcpRequest
{
    protected function rules(): array
    {
        return [
            'server' => ['required', 'string'],
            ...ListMcpInstructionVersionsAction::rules(),
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $versions = app(ListMcpInstructionVersionsAction::class)->execute($this->instruction());

        return $this->structuredCollection(McpInstructionVersionResource::collection($versions)->resolve());
    }
}
