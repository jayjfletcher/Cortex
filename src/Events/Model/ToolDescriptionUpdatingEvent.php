<?php

declare(strict_types=1);

namespace JayI\Cortex\Events\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Cortex\Contracts\ModelLifecycleEvent;
use JayI\Cortex\Models\ToolDescription;

/**
 * The ToolDescription `updating` Eloquent event.
 */
final class ToolDescriptionUpdatingEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public ToolDescription $description) {}

    public function model(): Model
    {
        return $this->description;
    }

    public function hook(): string
    {
        return 'updating';
    }
}
