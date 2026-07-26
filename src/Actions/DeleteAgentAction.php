<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

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
        $agent->delete();
    }
}
