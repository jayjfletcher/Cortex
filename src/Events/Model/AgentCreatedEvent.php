<?php

declare(strict_types=1);

namespace JayI\Cortex\Events\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Cortex\Contracts\ModelLifecycleEvent;
use JayI\Cortex\Models\Agent;

/**
 * The Agent `created` Eloquent event.
 */
final class AgentCreatedEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Agent $agent) {}

    public function model(): Model
    {
        return $this->agent;
    }

    public function hook(): string
    {
        return 'created';
    }
}
