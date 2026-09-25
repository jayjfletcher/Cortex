<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use JayI\Cortex\Events\Action\McpInstructionDeletedActionEvent;
use JayI\Cortex\Events\Action\McpInstructionDeletingActionEvent;
use JayI\Cortex\Models\McpInstruction;
use JayI\Cortex\Support\PublicationCache;

final class DeleteMcpInstructionAction
{
    public function __construct(private readonly PublicationCache $cache) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(McpInstruction $instruction): void
    {
        McpInstructionDeletingActionEvent::dispatch($instruction);

        $this->perform($instruction);

        McpInstructionDeletedActionEvent::dispatch($instruction);
    }

    private function perform(McpInstruction $instruction): void
    {
        $instruction->delete();

        $this->cache->forget($this->cache->mcpInstructionsKey());
    }
}
