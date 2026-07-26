<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\ListPromptsAction;
use JayI\Cortex\Http\Request;
use JayI\Cortex\Http\Resources\PromptResource;

final class IndexPromptsRequest extends Request
{
    public function rules(): array
    {
        return ListPromptsAction::rules();
    }

    public function persist(): JsonResponse
    {
        $prompts = app(ListPromptsAction::class)->execute();

        return PromptResource::collection($prompts)->response();
    }
}
