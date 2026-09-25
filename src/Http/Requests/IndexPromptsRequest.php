<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\ListPromptsAction;
use JayI\Cortex\Http\Request;
use JayI\Cortex\Http\Resources\PromptResource;
use JayI\Cortex\Models\Prompt;

final class IndexPromptsRequest extends Request
{
    public function authorize(): bool
    {
        return $this->allows('viewAny', Prompt::class);
    }

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
