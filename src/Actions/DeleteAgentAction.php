<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use JayI\Cortex\Events\Action\AgentDeletedActionEvent;
use JayI\Cortex\Events\Action\AgentDeletingActionEvent;
use JayI\Cortex\Models\Agent;

final class DeleteAgentAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(Agent $agent): void
    {
        AgentDeletingActionEvent::dispatch($agent);

        $this->perform($agent);

        AgentDeletedActionEvent::dispatch($agent);
    }

    private function perform(Agent $agent): void
    {
        $agent->delete();
    }
}
