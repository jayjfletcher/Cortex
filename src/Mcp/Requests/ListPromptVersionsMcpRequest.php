<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use JayI\Cortex\Actions\ListPromptVersionsAction;
use JayI\Cortex\Http\Resources\PromptVersionResource;
use Laravel\Mcp\ResponseFactory;

final class ListPromptVersionsMcpRequest extends PromptMcpRequest
{
    protected function rules(): array
    {
        return [
            'slug' => ['required', 'string'],
            ...ListPromptVersionsAction::rules(),
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $versions = app(ListPromptVersionsAction::class)->execute(
            $this->prompt(),
            isset($validated['page']) ? (int) $validated['page'] : null,
        );

        return $this->structuredCollection(PromptVersionResource::collection($versions)->resolve());
    }
}
