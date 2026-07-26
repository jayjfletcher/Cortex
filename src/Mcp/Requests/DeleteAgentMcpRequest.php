<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use JayI\Cortex\Actions\DeleteAgentAction;
use Laravel\Mcp\Response;

final class DeleteAgentMcpRequest extends AgentMcpRequest
{
    protected function rules(): array
    {
        return [
            'slug' => ['required', 'string'],
        ];
    }

    protected function handle(array $validated): Response
    {
        app(DeleteAgentAction::class)->execute($this->agent());

        return Response::text('Agent deleted.');
    }
}
