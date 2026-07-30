<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Http\Requests\IndexProvidersRequest;

final class ProviderController
{
    public function index(IndexProvidersRequest $request): JsonResponse
    {
        return $request->persist();
    }
}
