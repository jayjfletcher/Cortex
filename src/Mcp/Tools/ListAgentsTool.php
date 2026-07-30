<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Cortex\Mcp\Requests\ListAgentsMcpRequest;
use JayI\Cortex\Tools\Tool;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('List Cortex agents with their prompts, tools, and sub-agents. Paginated.')]
final class ListAgentsTool extends Tool
{
    public function handle(ListAgentsMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'page' => $schema->integer()->description('Page number, starting at 1.')->min(1),
        ];
    }
}
