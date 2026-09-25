<?php

declare(strict_types=1);

namespace JayI\Cortex\Events\Action;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Cortex\Contracts\ActionFinishedEvent;
use JayI\Cortex\Models\McpInstructionVersion;

/**
 * A new version of an MCP server's instructions override was created, and published when asked.
 */
final class McpInstructionVersionCreatedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public string $server,
        public McpInstructionVersion $version,
    ) {}
}
