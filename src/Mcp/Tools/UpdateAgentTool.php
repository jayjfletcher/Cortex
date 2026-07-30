<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Cortex\Mcp\Requests\UpdateAgentMcpRequest;
use JayI\Cortex\Mcp\Tools\Concerns\DescribesAgentPayload;
use JayI\Cortex\Tools\Tool;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Update a Cortex agent. The tools and sub_agents lists replace the current lists entirely; set prompt to change instructions.')]
final class UpdateAgentTool extends Tool
{
    use DescribesAgentPayload;

    public function handle(UpdateAgentMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('The agent slug.')->required(),
            'name' => $schema->string()->description('New display name.'),
            ...$this->agentPayloadSchema($schema),
        ];
    }
}
