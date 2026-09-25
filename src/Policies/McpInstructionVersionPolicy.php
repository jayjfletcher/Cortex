<?php

declare(strict_types=1);

namespace JayI\Cortex\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use JayI\Cortex\Models\McpInstruction;
use JayI\Cortex\Models\McpInstructionVersion;

/**
 * Versions belong to their override, so each check defers to it through the Gate:
 * reading a version needs `view` on the override, adding or publishing one needs
 * `update`. Versions are immutable, so nothing changes or deletes one.
 */
class McpInstructionVersionPolicy extends Policy
{
    public function viewAny(?Authenticatable $user, McpInstruction $instruction): bool
    {
        return $this->allowsOnParent($user, 'view', $instruction);
    }

    public function view(?Authenticatable $user, McpInstructionVersion $version): bool
    {
        return $this->allowsOnParent($user, 'view', $version->mcpInstruction);
    }

    public function create(?Authenticatable $user, McpInstruction $instruction): bool
    {
        return $this->allowsOnParent($user, 'update', $instruction);
    }

    public function publish(?Authenticatable $user, McpInstructionVersion $version): bool
    {
        return $this->allowsOnParent($user, 'update', $version->mcpInstruction);
    }
}
