<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use JayI\Cortex\Actions\ListMcpInstructionVersionsAction;
use JayI\Cortex\Http\Resources\McpInstructionVersionResource;
use JayI\Cortex\Models\McpInstructionVersion;
use Laravel\Mcp\ResponseFactory;

final class ListServerInstructionVersionsMcpRequest extends ServerMcpRequest
{
    protected function authorize(): bool
    {
        return $this->allows('viewAny', McpInstructionVersion::class, [$this->instruction()]);
    }

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
