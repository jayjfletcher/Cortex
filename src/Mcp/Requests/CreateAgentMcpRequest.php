<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use JayI\Cortex\Actions\CreateAgentAction;
use JayI\Cortex\Http\Resources\AgentResource;
use JayI\Cortex\Mcp\Request;
use JayI\Cortex\Models\Agent;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class CreateAgentMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return $this->allows('create', Agent::class);
    }

    protected function rules(): array
    {
        return CreateAgentAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $agent = app(CreateAgentAction::class)->execute($validated);

        return Response::structured((new AgentResource($agent))->resolve());
    }
}
