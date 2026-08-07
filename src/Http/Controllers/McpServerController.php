<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Http\Requests\IndexMcpServersRequest;

final class McpServerController
{
    public function index(IndexMcpServersRequest $request): JsonResponse
    {
        return $request->persist();
    }
}
