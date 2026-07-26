<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Cortex\Mcp\Requests\ShowPromptVersionMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Show a specific version of a Cortex prompt by version number.')]
final class ShowPromptVersionTool extends Tool
{
    public function handle(ShowPromptVersionMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('The prompt slug.')->required(),
            'version' => $schema->integer()->description('The version number.')->min(1)->required(),
        ];
    }
}
