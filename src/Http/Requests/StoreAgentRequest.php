<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\CreateAgentAction;
use JayI\Cortex\Http\Request;
use JayI\Cortex\Http\Resources\AgentResource;
use JayI\Cortex\Models\Agent;

final class StoreAgentRequest extends Request
{
    public function authorize(): bool
    {
        return $this->allows('create', Agent::class);
    }

    public function rules(): array
    {
        return CreateAgentAction::rules();
    }

    public function persist(): JsonResponse
    {
        $agent = app(CreateAgentAction::class)->execute($this->validated());

        return (new AgentResource($agent))->response()->setStatusCode(201);
    }
}
