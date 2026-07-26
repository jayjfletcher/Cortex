<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use Illuminate\Support\Arr;
use JayI\Cortex\Actions\UpdateAgentAction;
use JayI\Cortex\Http\Resources\AgentResource;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

final class UpdateAgentMcpRequest extends AgentMcpRequest
{
    protected function rules(): array
    {
        return [
            'slug' => ['required', 'string'],
            ...UpdateAgentAction::rules(),
        ];
    }

    protected function handle(array $validated): ResponseFactory
    {
        $agent = app(UpdateAgentAction::class)->execute(
            $this->agent(),
            Arr::except($validated, ['slug']),
        );

        return Response::structured((new AgentResource($agent))->resolve());
    }
}
