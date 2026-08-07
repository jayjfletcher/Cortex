<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

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
        $instruction->delete();

        $this->cache->forget($this->cache->mcpInstructionsKey());
    }
}
