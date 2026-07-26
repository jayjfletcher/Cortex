<?php

declare(strict_types=1);

namespace JayI\Cortex\Exceptions;

use JayI\Cortex\Models\Prompt;
use RuntimeException;

final class PromptNotPublishedException extends RuntimeException
{
    public static function forPrompt(Prompt $prompt): self
    {
        return new self("Prompt [{$prompt->slug}] has no published version.");
    }
}
