<?php

declare(strict_types=1);

namespace JayI\Cortex\Events\Action;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Cortex\Contracts\ActionFinishedEvent;
use JayI\Cortex\Models\ToolDescriptionVersion;

/**
 * A new version of a tool's description override was created, and published when asked.
 */
final class ToolDescriptionVersionCreatedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public string $tool,
        public ToolDescriptionVersion $version,
    ) {}
}
