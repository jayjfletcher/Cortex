<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use JayI\Cortex\Actions\ShowPromptAction;
use JayI\Cortex\Http\Resources\PromptResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class ShowPromptMcpRequest extends PromptMcpRequest
{
    protected function rules(): array
    {
        return [
            'slug' => ['required', 'string'],
            ...ShowPromptAction::rules(),
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $prompt = app(ShowPromptAction::class)->execute($this->prompt());

        return Response::structured((new PromptResource($prompt))->resolve());
    }
}
