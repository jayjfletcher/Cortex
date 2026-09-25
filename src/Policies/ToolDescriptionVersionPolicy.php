<?php

declare(strict_types=1);

namespace JayI\Cortex\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use JayI\Cortex\Models\ToolDescription;
use JayI\Cortex\Models\ToolDescriptionVersion;

/**
 * Versions belong to their override, so each check defers to it through the Gate:
 * reading a version needs `view` on the override, adding or publishing one needs
 * `update`. Versions are immutable, so nothing changes or deletes one.
 */
class ToolDescriptionVersionPolicy extends Policy
{
    public function viewAny(?Authenticatable $user, ToolDescription $description): bool
    {
        return $this->allowsOnParent($user, 'view', $description);
    }

    public function view(?Authenticatable $user, ToolDescriptionVersion $version): bool
    {
        return $this->allowsOnParent($user, 'view', $version->toolDescription);
    }

    public function create(?Authenticatable $user, ToolDescription $description): bool
    {
        return $this->allowsOnParent($user, 'update', $description);
    }

    public function publish(?Authenticatable $user, ToolDescriptionVersion $version): bool
    {
        return $this->allowsOnParent($user, 'update', $version->toolDescription);
    }
}
