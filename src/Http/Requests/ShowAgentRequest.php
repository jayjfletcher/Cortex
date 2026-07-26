<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\ShowAgentAction;
use JayI\Cortex\Http\Resources\AgentResource;

final class ShowAgentRequest extends AgentRequest
{
    public function rules(): array
    {
        return ShowAgentAction::rules();
    }

    public function persist(): JsonResponse
    {
        $agent = app(ShowAgentAction::class)->execute($this->agent());

        return (new AgentResource($agent))->response();
    }
}
