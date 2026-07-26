<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use JayI\Cortex\Mcp\Request;
use JayI\Cortex\Models\Prompt;

abstract class PromptMcpRequest extends Request
{
    private ?Prompt $prompt = null;

    protected function prompt(): Prompt
    {
        return $this->prompt ??= Prompt::query()
            ->where('slug', $this->get('slug'))
            ->firstOrFail();
    }
}
