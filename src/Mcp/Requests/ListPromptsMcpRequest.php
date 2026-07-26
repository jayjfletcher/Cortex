<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use JayI\Cortex\Actions\ListPromptsAction;
use JayI\Cortex\Http\Resources\PromptResource;
use JayI\Cortex\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

final class ListPromptsMcpRequest extends Request
{
    protected function rules(): array
    {
        return ListPromptsAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $prompts = app(ListPromptsAction::class)->execute(
            isset($validated['page']) ? (int) $validated['page'] : null,
        );

        return $this->structuredCollection(PromptResource::collection($prompts)->resolve());
    }
}
