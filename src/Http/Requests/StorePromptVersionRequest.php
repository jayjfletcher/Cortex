<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\CreatePromptVersionAction;
use JayI\Cortex\Http\Resources\PromptVersionResource;
use JayI\Cortex\Models\PromptVersion;

final class StorePromptVersionRequest extends PromptRequest
{
    public function authorize(): bool
    {
        return $this->allows('create', PromptVersion::class, [$this->prompt()]);
    }

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
