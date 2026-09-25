<?php

declare(strict_types=1);

namespace JayI\Cortex\Events\Action;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Cortex\Contracts\ActionFinishedEvent;
use JayI\Cortex\Models\ToolDescription;
use JayI\Cortex\Models\ToolDescriptionVersion;

/**
 * The versions of a tool's description override were listed.
 */
final class ToolDescriptionVersionsListedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  Collection<int, ToolDescriptionVersion>  $versions
     */
    public function __construct(
        public ToolDescription $description,
        public Collection $versions,
    ) {}
}
