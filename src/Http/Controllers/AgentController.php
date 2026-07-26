<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use JayI\Cortex\Http\Requests\DeleteAgentRequest;
use JayI\Cortex\Http\Requests\IndexAgentsRequest;
use JayI\Cortex\Http\Requests\ShowAgentRequest;
use JayI\Cortex\Http\Requests\StoreAgentRequest;
use JayI\Cortex\Http\Requests\UpdateAgentRequest;
use JayI\Cortex\Models\Agent;

final class AgentController
{
    public function index(IndexAgentsRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function store(StoreAgentRequest $request): JsonResponse
    {
        return $request->persist();
    }

    public function show(ShowAgentRequest $request, Agent $agent): JsonResponse
    {
        return $request->persist();
    }

    public function update(UpdateAgentRequest $request, Agent $agent): JsonResponse
    {
        return $request->persist();
    }

    public function destroy(DeleteAgentRequest $request, Agent $agent): Response
    {
        return $request->persist();
    }
}
