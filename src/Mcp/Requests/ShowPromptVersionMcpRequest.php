<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use JayI\Cortex\Actions\ShowPromptVersionAction;
use JayI\Cortex\Http\Resources\PromptVersionResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class ShowPromptVersionMcpRequest extends PromptMcpRequest
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
        $version = app(ShowPromptVersionAction::class)->execute(
            $this->prompt(),
            (int) $validated['version'],
        );

        return Response::structured((new PromptVersionResource($version))->resolve());
    }
}
