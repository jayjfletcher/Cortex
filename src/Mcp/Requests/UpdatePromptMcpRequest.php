<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use Illuminate\Support\Arr;
use JayI\Cortex\Actions\UpdatePromptAction;
use JayI\Cortex\Http\Resources\PromptResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class UpdatePromptMcpRequest extends PromptMcpRequest
{
    protected function rules(): array
    {
        return [
            'slug' => ['required', 'string'],
            ...UpdatePromptAction::rules(),
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $prompt = app(UpdatePromptAction::class)->execute(
            $this->prompt(),
            Arr::except($validated, ['slug']),
        );

        return Response::structured((new PromptResource($prompt))->resolve());
    }
}
