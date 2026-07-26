<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\PublishPromptVersionAction;
use JayI\Cortex\Http\Resources\PromptResource;

final class PublishPromptVersionRequest extends PromptRequest
{
    public function rules(): array
    {
        return PublishPromptVersionAction::rules();
    }

    public function persist(): JsonResponse
    {
        $prompt = app(PublishPromptVersionAction::class)->execute(
            $this->prompt(),
            (int) $this->route('version'),
        );

        return (new PromptResource($prompt))->response();
    }
}
