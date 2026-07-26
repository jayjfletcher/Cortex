<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Cortex\Mcp\Requests\CreatePromptVersionMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Create a new immutable version of a Cortex prompt. Not published unless requested.')]
final class CreatePromptVersionTool extends Tool
{
    public function handle(CreatePromptVersionMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('The prompt slug.')->required(),
            'content' => $schema->string()->description('The new version\'s content.')->required(),
            'publish' => $schema->boolean()->description('Publish this version immediately. Defaults to false.'),
        ];
    }
}
