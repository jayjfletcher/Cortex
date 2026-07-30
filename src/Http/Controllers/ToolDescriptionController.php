<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use JayI\Cortex\Http\Requests\DeleteToolDescriptionRequest;
use JayI\Cortex\Http\Requests\IndexToolDescriptionVersionsRequest;
use JayI\Cortex\Http\Requests\PublishToolDescriptionVersionRequest;
use JayI\Cortex\Http\Requests\ShowToolDescriptionRequest;
use JayI\Cortex\Http\Requests\StoreToolDescriptionVersionRequest;

final class ToolDescriptionController
{
    public function show(ShowToolDescriptionRequest $request, string $tool): JsonResponse
    {
        return $request->persist();
    }

    public function destroy(DeleteToolDescriptionRequest $request, string $tool): Response
    {
        return $request->persist();
    }

    public function versions(IndexToolDescriptionVersionsRequest $request, string $tool): JsonResponse
    {
        return $request->persist();
    }

    public function store(StoreToolDescriptionVersionRequest $request, string $tool): JsonResponse
    {
        return $request->persist();
    }

    public function publish(PublishToolDescriptionVersionRequest $request, string $tool, int $version): JsonResponse
    {
        return $request->persist();
    }
}
