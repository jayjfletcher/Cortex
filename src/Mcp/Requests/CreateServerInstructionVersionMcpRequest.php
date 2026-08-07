<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use Illuminate\Support\Arr;
use JayI\Cortex\Actions\CreateMcpInstructionVersionAction;
use JayI\Cortex\Http\Resources\McpInstructionVersionResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class CreateServerInstructionVersionMcpRequest extends ServerMcpRequest
{
    protected function rules(): array
    {
        return [
            'server' => ['required', 'string'],
            ...CreateMcpInstructionVersionAction::rules(),
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $version = app(CreateMcpInstructionVersionAction::class)->execute(
            $this->serverName(),
            Arr::except($validated, ['server']),
        );

        return Response::structured((new McpInstructionVersionResource($version))->resolve());
    }
}
