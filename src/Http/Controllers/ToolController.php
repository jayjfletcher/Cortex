<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Http\Requests\IndexToolsRequest;

final class ToolController
{
    public function index(IndexToolsRequest $request): JsonResponse
    {
        return $request->persist();
    }
}
