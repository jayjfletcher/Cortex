<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Cortex\Mcp\Requests\CreatePromptMcpRequest;
use JayI\Cortex\Tools\Tool;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;

#[Description('Create a Cortex prompt. Its content is stored as immutable version 1, published by default.')]
final class CreatePromptTool extends Tool
{
    public function handle(CreatePromptMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Display name for the prompt.')->required(),
            'slug' => $schema->string()->description('Unique identifier (letters, numbers, dashes, underscores).')->required(),
            'description' => $schema->string()->description('Optional description of what the prompt is for.'),
            'content' => $schema->string()->description('The prompt content, stored as version 1.')->required(),
            'publish' => $schema->boolean()->description('Publish version 1 immediately. Defaults to true.'),
        ];
    }
}
