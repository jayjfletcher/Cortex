<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Cortex\Mcp\Requests\ListPromptVersionsMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List a Cortex prompt\'s versions, newest first. Paginated.')]
final class ListPromptVersionsTool extends Tool
{
    public function handle(ListPromptVersionsMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('The prompt slug.')->required(),
            'page' => $schema->integer()->description('Page number, starting at 1.')->min(1),
        ];
    }
}
