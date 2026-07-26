<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\Response;
use JayI\Cortex\Actions\DeleteAgentAction;

final class DeleteAgentRequest extends AgentRequest
{
    public function persist(): Response
    {
        app(DeleteAgentAction::class)->execute($this->agent());

        return response()->noContent();
    }
}
