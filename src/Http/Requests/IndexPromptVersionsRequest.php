<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\ListPromptVersionsAction;
use JayI\Cortex\Http\Resources\PromptVersionResource;

final class IndexPromptVersionsRequest extends PromptRequest
{
    public function rules(): array
    {
        return ListPromptVersionsAction::rules();
    }

    public function persist(): JsonResponse
    {
        $versions = app(ListPromptVersionsAction::class)->execute($this->prompt());

        return PromptVersionResource::collection($versions)->response();
    }
}
