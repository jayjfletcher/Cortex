<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Cortex\Mcp\Requests\CreateAgentMcpRequest;
use JayI\Cortex\Mcp\Tools\Concerns\DescribesAgentPayload;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Create a Cortex agent. Attach a prompt for instructions, registered tools, and sub-agents to delegate to.')]
final class CreateAgentTool extends Tool
{
    use DescribesAgentPayload;

    public function handle(CreateAgentMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Display name for the agent.')->required(),
            'slug' => $schema->string()->description('Unique identifier (letters, numbers, dashes, underscores).')->required(),
            ...$this->agentPayloadSchema($schema),
        ];
    }
}
