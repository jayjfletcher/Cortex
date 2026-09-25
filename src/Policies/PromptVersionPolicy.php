<?php

declare(strict_types=1);

namespace JayI\Cortex\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Models\PromptVersion;

/**
 * Versions belong to their prompt, so each check defers to it through the Gate:
 * reading a version needs `view` on the prompt, adding or publishing one needs
 * `update`. Versions are immutable, so nothing changes or deletes one.
 */
class PromptVersionPolicy extends Policy
{
    public function viewAny(?Authenticatable $user, Prompt $prompt): bool
    {
        return $this->allowsOnParent($user, 'view', $prompt);
    }

    public function view(?Authenticatable $user, PromptVersion $version): bool
    {
        return $this->allowsOnParent($user, 'view', $version->prompt);
    }

    public function create(?Authenticatable $user, Prompt $prompt): bool
    {
        return $this->allowsOnParent($user, 'update', $prompt);
    }

    public function publish(?Authenticatable $user, PromptVersion $version): bool
    {
        return $this->allowsOnParent($user, 'update', $version->prompt);
    }
}
