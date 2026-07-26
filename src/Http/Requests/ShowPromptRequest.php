<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\ShowPromptAction;
use JayI\Cortex\Http\Resources\PromptResource;

final class ShowPromptRequest extends PromptRequest
{
    public function rules(): array
    {
        return ShowPromptAction::rules();
    }

    public function persist(): JsonResponse
    {
        $prompt = app(ShowPromptAction::class)->execute($this->prompt());

        return (new PromptResource($prompt))->response();
    }
}
