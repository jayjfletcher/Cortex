<?php

declare(strict_types=1);

namespace JayI\Cortex\Tests\Fixtures\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use JayI\Cortex\Models\ToolDescription;
use JayI\Cortex\Policies\ToolDescriptionPolicy;

/**
 * No tool description may be overridden.
 */
final class FrozenToolDescriptionPolicy extends ToolDescriptionPolicy
{
    public function update(?Authenticatable $user, ToolDescription $description): bool
    {
        return false;
    }
}
