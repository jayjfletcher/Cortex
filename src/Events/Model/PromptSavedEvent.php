<?php

declare(strict_types=1);

namespace JayI\Cortex\Events\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Cortex\Contracts\ModelLifecycleEvent;
use JayI\Cortex\Models\Prompt;

/**
 * The Prompt `saved` Eloquent event.
 */
final class PromptSavedEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Prompt $prompt) {}

    public function model(): Model
    {
        return $this->prompt;
    }

    public function hook(): string
    {
        return 'saved';
    }
}
