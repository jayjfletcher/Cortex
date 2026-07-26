<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\CreatePromptAction;
use JayI\Cortex\Http\Request;
use JayI\Cortex\Http\Resources\PromptResource;

final class StorePromptRequest extends Request
{
    public function rules(): array
    {
        return CreatePromptAction::rules();
    }

    public function persist(): JsonResponse
    {
        $prompt = app(CreatePromptAction::class)->execute($this->validated());

        return (new PromptResource($prompt))->response()->setStatusCode(201);
    }
}
