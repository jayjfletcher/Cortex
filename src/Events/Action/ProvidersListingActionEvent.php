<?php

declare(strict_types=1);

namespace JayI\Cortex\Events\Action;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Cortex\Contracts\ActionStartingEvent;

/**
 * The providers agents can run on are about to be listed.
 */
final class ProvidersListingActionEvent implements ActionStartingEvent
{
    use Dispatchable;
    use SerializesModels;
}
