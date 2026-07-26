<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Cortex\Mcp\Requests\PublishPromptVersionMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Publish a specific version of a Cortex prompt, making it the version agents use by default.')]
final class PublishPromptVersionTool extends Tool
{
    public function handle(PublishPromptVersionMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('The prompt slug.')->required(),
            'version' => $schema->integer()->description('The version number to publish.')->min(1)->required(),
        ];
    }
}
