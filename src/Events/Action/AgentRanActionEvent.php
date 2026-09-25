<?php

declare(strict_types=1);

namespace JayI\Cortex\Events\Action;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Cortex\Contracts\ActionFinishedEvent;
use JayI\Cortex\Models\Agent;
use Laravel\Ai\Responses\AgentResponse;

/**
 * An agent ran and responded.
 */
final class AgentRanActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Agent $agent,
        public string $input,
        public AgentResponse $response,
    ) {}
}
