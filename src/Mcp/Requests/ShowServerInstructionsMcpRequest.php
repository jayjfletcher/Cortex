<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use JayI\Cortex\Actions\ShowMcpInstructionAction;
use JayI\Cortex\Http\Resources\McpInstructionResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class ShowServerInstructionsMcpRequest extends ServerMcpRequest
{
    protected function rules(): array
    {
        return [
            'server' => ['required', 'string'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $instruction = app(ShowMcpInstructionAction::class)->execute($this->serverName());

        return Response::structured((new McpInstructionResource($instruction))->resolve());
    }
}
