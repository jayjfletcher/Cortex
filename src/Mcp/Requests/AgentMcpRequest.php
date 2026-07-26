<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use JayI\Cortex\Mcp\Request;
use JayI\Cortex\Models\Agent;

abstract class AgentMcpRequest extends Request
{
    private ?Agent $agent = null;

    protected function agent(): Agent
    {
        return $this->agent ??= Agent::query()
            ->where('slug', $this->get('slug'))
            ->firstOrFail();
    }
}
