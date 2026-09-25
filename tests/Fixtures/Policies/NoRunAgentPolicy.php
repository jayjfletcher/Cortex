<?php

declare(strict_types=1);

namespace JayI\Cortex\Tests\Fixtures\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use JayI\Cortex\Models\Agent;
use JayI\Cortex\Policies\AgentPolicy;

/**
 * Agents may be managed but never run.
 */
final class NoRunAgentPolicy extends AgentPolicy
{
    public function run(?Authenticatable $user, Agent $agent): bool
    {
        return false;
    }
}
