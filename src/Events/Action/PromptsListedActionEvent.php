<?php

declare(strict_types=1);

namespace JayI\Cortex\Events\Action;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Cortex\Contracts\ActionFinishedEvent;
use JayI\Cortex\Models\Prompt;

/**
 * A page of prompts was listed.
 */
final class PromptsListedActionEvent implements ActionFinishedEvent
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @param  LengthAwarePaginator<int, Prompt>  $prompts
     */
    public function __construct(
        public LengthAwarePaginator $prompts,
    ) {}
}
