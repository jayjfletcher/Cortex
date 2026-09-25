<?php

declare(strict_types=1);

namespace JayI\Cortex\Events\Action;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Cortex\Contracts\ActionFinishedEvent;
use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Models\PromptVersion;

/**
 * A page of a prompt's versions was listed.
 */
final class PromptVersionsListedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  LengthAwarePaginator<int, PromptVersion>  $versions
     */
    public function __construct(
        public Prompt $prompt,
        public LengthAwarePaginator $versions,
    ) {}
}
