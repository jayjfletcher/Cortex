<?php

declare(strict_types=1);

namespace JayI\Cortex\Events\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JayI\Cortex\Contracts\ModelLifecycleEvent;
use JayI\Cortex\Models\McpInstruction;

/**
 * The McpInstruction `creating` Eloquent event.
 */
final class McpInstructionCreatingEvent implements ModelLifecycleEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public McpInstruction $instruction) {}

    public function model(): Model
    {
        return $this->instruction;
    }

    public function hook(): string
    {
        return 'creating';
    }
}
