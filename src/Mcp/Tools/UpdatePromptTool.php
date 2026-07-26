<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use JayI\Cortex\Mcp\Requests\UpdatePromptMcpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Update a Cortex prompt\'s name or description. Content changes require creating a new version instead.')]
final class UpdatePromptTool extends Tool
{
    public function handle(UpdatePromptMcpRequest $request): Response|ResponseFactory
    {
        return $request->persist();
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'slug' => $schema->string()->description('The prompt slug.')->required(),
            'name' => $schema->string()->description('New display name.'),
            'description' => $schema->string()->description('New description.'),
        ];
    }
}
