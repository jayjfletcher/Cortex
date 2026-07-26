<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\UpdatePromptAction;
use JayI\Cortex\Http\Resources\PromptResource;

final class UpdatePromptRequest extends PromptRequest
{
    public function rules(): array
    {
        return UpdatePromptAction::rules();
    }

    public function persist(): JsonResponse
    {
        $prompt = app(UpdatePromptAction::class)->execute($this->prompt(), $this->validated());

        return (new PromptResource($prompt))->response();
    }
}
