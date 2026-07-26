<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use JayI\Cortex\Http\Requests\DeletePromptRequest;
use JayI\Cortex\Http\Requests\IndexPromptsRequest;
use JayI\Cortex\Http\Requests\ShowPromptRequest;
use JayI\Cortex\Http\Requests\StorePromptRequest;
use JayI\Cortex\Http\Requests\UpdatePromptRequest;
use JayI\Cortex\Models\Prompt;

final class PromptController
{
    public function index(IndexPromptsRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function store(StorePromptRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function show(ShowPromptRequest $request, Prompt $prompt): JsonResponse
    {
        return $request->persist();
    }

    public function update(UpdatePromptRequest $request, Prompt $prompt): JsonResponse
    {
        return $request->persist();
    }

    public function destroy(DeletePromptRequest $request, Prompt $prompt): Response
    {
        return $request->persist();
    }
}
