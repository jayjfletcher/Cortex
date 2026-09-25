<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use JayI\Cortex\Actions\ListAgentsAction;
use JayI\Cortex\Http\Resources\AgentResource;
use JayI\Cortex\Mcp\Request;
use JayI\Cortex\Models\Agent;
use Laravel\Mcp\ResponseFactory;

final class ListAgentsMcpRequest extends Request
{
    protected function authorize(): bool
    {
        return $this->allows('viewAny', Agent::class);
    }

    protected function rules(): array
    {
        return ListAgentsAction::rules();
    }

    protected function handle(array $validated): ResponseFactory
    {
        $agents = app(ListAgentsAction::class)->execute(
            isset($validated['page']) ? (int) $validated['page'] : null,
        );

        return $this->structuredCollection(AgentResource::collection($agents)->resolve());
    }
}
