<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use JayI\Cortex\Actions\ShowAgentAction;
use JayI\Cortex\Http\Resources\AgentResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class ShowAgentMcpRequest extends AgentMcpRequest
{
    protected function rules(): array
    {
        return [
            'slug' => ['required', 'string'],
            ...ShowAgentAction::rules(),
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $agent = app(ShowAgentAction::class)->execute($this->agent());

        return Response::structured((new AgentResource($agent))->resolve());
    }
}
