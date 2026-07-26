<?php

declare(strict_types=1);

namespace JayI\Cortex\Exceptions;

use JayI\Cortex\Models\Agent;
use RuntimeException;

final class CircularAgentReferenceException extends RuntimeException
{
    public static function forAgent(Agent $agent): self
    {
        return new self("Agent [{$agent->slug}] is part of a circular sub-agent reference.");
    }
}
