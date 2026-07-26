<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use JayI\Cortex\Models\Agent;

final class ShowAgentAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(Agent $agent): Agent
    {
        return $agent->load(['prompt', 'pinnedVersion', 'subAgents']);
    }
}
