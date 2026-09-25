<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use JayI\Cortex\Http\Request;
use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Models\PromptVersion;

abstract class PromptRequest extends Request
{
    protected function prompt(): Prompt
    {
        $prompt = $this->route('prompt');

        if (! $prompt instanceof Prompt) {
            abort(404);
        }

        return $prompt;
    }

    /**
     * The version named in the route.
     */
    protected function version(): PromptVersion
    {
        /** @var PromptVersion */
        return $this->prompt()->versions()->where('version', (int) $this->route('version'))->firstOrFail();
    }
}
