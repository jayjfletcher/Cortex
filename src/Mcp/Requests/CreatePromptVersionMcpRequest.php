<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use Illuminate\Support\Arr;
use JayI\Cortex\Actions\CreatePromptVersionAction;
use JayI\Cortex\Http\Resources\PromptVersionResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class CreatePromptVersionMcpRequest extends PromptMcpRequest
{
    protected function rules(): array
    {
        return [
            'slug' => ['required', 'string'],
            ...CreatePromptVersionAction::rules(),
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $version = app(CreatePromptVersionAction::class)->execute(
            $this->prompt(),
            Arr::except($validated, ['slug']),
        );

        return Response::structured((new PromptVersionResource($version))->resolve());
    }
}
