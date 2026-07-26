<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\CreatePromptVersionAction;
use JayI\Cortex\Http\Resources\PromptVersionResource;

final class StorePromptVersionRequest extends PromptRequest
{
    public function rules(): array
    {
        return CreatePromptVersionAction::rules();
    }

    public function persist(): JsonResponse
    {
        $version = app(CreatePromptVersionAction::class)->execute($this->prompt(), $this->validated());

        return (new PromptVersionResource($version))->response()->setStatusCode(201);
    }
}
