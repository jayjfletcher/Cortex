<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\RunAgentAction;
use JayI\Cortex\Http\Resources\AgentRunResource;

final class RunAgentRequest extends AgentRequest
{
    public function rules(): array
    {
        return RunAgentAction::rules();
    }

    public function persist(): JsonResponse
    {
        $response = app(RunAgentAction::class)->execute(
            $this->agent(),
            $this->string('input')->value(),
        );

        return (new AgentRunResource($response))->response();
    }
}
