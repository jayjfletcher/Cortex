<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\ShowPromptVersionAction;
use JayI\Cortex\Http\Resources\PromptVersionResource;

final class ShowPromptVersionRequest extends PromptRequest
{
    public function rules(): array
    {
        return ShowPromptVersionAction::rules();
    }

    public function persist(): JsonResponse
    {
        $version = app(ShowPromptVersionAction::class)->execute(
            $this->prompt(),
            (int) $this->route('version'),
        );

        return (new PromptVersionResource($version))->response();
    }
}
