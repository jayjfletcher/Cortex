<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\Response;
use JayI\Cortex\Actions\DeleteMcpInstructionAction;

final class DeleteMcpInstructionRequest extends McpInstructionRequest
{
    public function persist(): Response
    {
        app(DeleteMcpInstructionAction::class)->execute($this->instruction());

        return response()->noContent();
    }
}
