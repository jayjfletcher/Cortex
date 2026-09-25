<?php

declare(strict_types=1);

namespace JayI\Cortex\Tests\Fixtures\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Policies\PromptPolicy;

/**
 * Nobody may change a prompt, or add or publish its versions.
 */
final class ReadOnlyPromptPolicy extends PromptPolicy
{
    public function update(?Authenticatable $user, Prompt $prompt): bool
    {
        return false;
    }
}
