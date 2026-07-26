<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Cortex\Mcp\Requests\RunAgentMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Run a Cortex agent with the given input and return its response text and token usage.')]
final class RunAgentTool extends Tool
{
    public function handle(RunAgentMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('The agent slug.')->required(),
            'input' => $schema->string()->description('The user input to send to the agent.')->required(),
        ];
    }
}
