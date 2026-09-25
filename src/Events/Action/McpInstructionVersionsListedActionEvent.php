<?php

declare(strict_types=1);

namespace JayI\Cortex\Events\Action;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Cortex\Contracts\ActionFinishedEvent;
use JayI\Cortex\Models\McpInstruction;
use JayI\Cortex\Models\McpInstructionVersion;

/**
 * The versions of an MCP server's instructions override were listed.
 */
final class McpInstructionVersionsListedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  Collection<int, McpInstructionVersion>  $versions
     */
    public function __construct(
        public McpInstruction $instruction,
        public Collection $versions,
    ) {}
}
