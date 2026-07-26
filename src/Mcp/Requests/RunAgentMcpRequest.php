<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use JayI\Cortex\Actions\RunAgentAction;
use JayI\Cortex\Http\Resources\AgentRunResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class RunAgentMcpRequest extends AgentMcpRequest
{
    protected function rules(): array
    {
        return [
            'slug' => ['required', 'string'],
            ...RunAgentAction::rules(),
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $response = app(RunAgentAction::class)->execute(
            $this->agent(),
            (string) $validated['input'],
        );

        return Response::structured((new AgentRunResource($response))->resolve());
    }
}
