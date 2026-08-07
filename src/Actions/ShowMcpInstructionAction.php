<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use JayI\Cortex\Models\McpInstruction;

final class ShowMcpInstructionAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(string $server): McpInstruction
    {
        return McpInstruction::query()
            ->where('server', $server)
            ->with('publishedVersion')
            ->firstOrFail();
    }
}
