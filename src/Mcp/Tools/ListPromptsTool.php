<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Cortex\Mcp\Requests\ListPromptsMcpRequest;
use JayI\Cortex\Tools\Tool;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('List Cortex prompts with their published versions. Paginated.')]
final class ListPromptsTool extends Tool
{
    public function handle(ListPromptsMcpRequest $request): Response|ResponseFactory
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
