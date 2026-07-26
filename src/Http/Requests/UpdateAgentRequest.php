<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\UpdateAgentAction;
use JayI\Cortex\Http\Resources\AgentResource;

final class UpdateAgentRequest extends AgentRequest
{
    public function rules(): array
    {
        return UpdateAgentAction::rules();
    }

    public function persist(): JsonResponse
    {
        $agent = app(UpdateAgentAction::class)->execute($this->agent(), $this->validated());

        return (new AgentResource($agent))->response();
    }
}
