<?php

declare(strict_types=1);

namespace JayI\Cortex\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use JayI\Cortex\Models\Prompt;

/**
 * Answers `$user->can(...)` for prompts.
 *
 * Prompts have no owner, so every ability is allowed, for guests too: the route
 * middleware in front of the API and MCP server decides who gets in. Point
 * `cortex.policies` at your own class to restrict them.
 */
class PromptPolicy extends Policy
{
    public function viewAny(?Authenticatable $user): bool
    {
        return true;
    }

    public function create(?Authenticatable $user): bool
    {
        return true;
    }

    public function view(?Authenticatable $user, Prompt $prompt): bool
    {
        return true;
    }

    public function update(?Authenticatable $user, Prompt $prompt): bool
    {
        return true;
    }

    public function delete(?Authenticatable $user, Prompt $prompt): bool
    {
        return true;
    }
}
