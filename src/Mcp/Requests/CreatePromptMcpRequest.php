<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use JayI\Cortex\Actions\CreatePromptAction;
use JayI\Cortex\Http\Resources\PromptResource;
use JayI\Cortex\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class CreatePromptMcpRequest extends Request
{
    protected function rules(): array
    {
        return CreatePromptAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $prompt = app(CreatePromptAction::class)->execute($validated);

        return Response::structured((new PromptResource($prompt))->resolve());
    }
}
