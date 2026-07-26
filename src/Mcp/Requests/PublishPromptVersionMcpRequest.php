<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use JayI\Cortex\Actions\PublishPromptVersionAction;
use JayI\Cortex\Http\Resources\PromptResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class PublishPromptVersionMcpRequest extends PromptMcpRequest
{
    protected function rules(): array
    {
        return [
            'slug' => ['required', 'string'],
            'version' => ['required', 'integer', 'min:1'],
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $prompt = app(PublishPromptVersionAction::class)->execute(
            $this->prompt(),
            (int) $validated['version'],
        );

        return Response::structured((new PromptResource($prompt))->resolve());
    }
}
