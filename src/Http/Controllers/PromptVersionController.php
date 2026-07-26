<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Http\Requests\IndexPromptVersionsRequest;
use JayI\Cortex\Http\Requests\PublishPromptVersionRequest;
use JayI\Cortex\Http\Requests\ShowPromptVersionRequest;
use JayI\Cortex\Http\Requests\StorePromptVersionRequest;
use JayI\Cortex\Models\Prompt;

final class PromptVersionController
{
    public function index(IndexPromptVersionsRequest $request, Prompt $prompt): JsonResponse
    {
        return $request->persist();
    }

    public function store(StorePromptVersionRequest $request, Prompt $prompt): JsonResponse
    {
        return $request->persist();
    }

    public function show(ShowPromptVersionRequest $request, Prompt $prompt, int $version): JsonResponse
    {
        return $request->persist();
    }

    public function publish(PublishPromptVersionRequest $request, Prompt $prompt, int $version): JsonResponse
    {
        return $request->persist();
    }
}
