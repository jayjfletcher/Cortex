<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use JayI\Cortex\Http\Request;
use JayI\Cortex\Models\Agent;

abstract class AgentRequest extends Request
{
    protected function agent(): Agent
    {
        $agent = $this->route('agent');

        if (! $agent instanceof Agent) {
            abort(404);
        }

        return $agent;
    }
}
