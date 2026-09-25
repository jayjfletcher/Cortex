<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use Illuminate\Database\Eloquent\Collection;
use JayI\Cortex\Events\Action\McpInstructionVersionsListedActionEvent;
use JayI\Cortex\Events\Action\McpInstructionVersionsListingActionEvent;
use JayI\Cortex\Models\McpInstruction;
use JayI\Cortex\Models\McpInstructionVersion;

final class ListMcpInstructionVersionsAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    /**
     * @return Collection<int, McpInstructionVersion>
     */
    public function execute(McpInstruction $instruction): Collection
    {
        McpInstructionVersionsListingActionEvent::dispatch($instruction);

        $result = $this->perform($instruction);

        McpInstructionVersionsListedActionEvent::dispatch($instruction, $result);

        return $result;
    }

    /**
     * @return Collection<int, McpInstructionVersion>
     */
    private function perform(McpInstruction $instruction): Collection
    {
        return $instruction->versions()->orderByDesc('version')->get();
    }
}
