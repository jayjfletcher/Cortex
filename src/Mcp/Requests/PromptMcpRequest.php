<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use JayI\Cortex\Mcp\Request;
use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Models\PromptVersion;

abstract class PromptMcpRequest extends Request
{
    private ?Prompt $prompt = null;

    protected function prompt(): Prompt
    {
        return $this->prompt ??= Prompt::query()
            ->where('slug', $this->get('slug'))
            ->firstOrFail();
    }

    /**
     * The version named in the input.
     */
    protected function version(): PromptVersion
    {
        /** @var PromptVersion */
        return $this->prompt()->versions()->where('version', (int) $this->get('version'))->firstOrFail();
    }
}
