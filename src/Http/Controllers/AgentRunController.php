<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Controllers;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Http\Requests\RunAgentRequest;
use JayI\Cortex\Models\Agent;

final class AgentRunController
{
    public function store(RunAgentRequest $request, Agent $agent): JsonResponse
    {
        return $request->persist();
    }
}
