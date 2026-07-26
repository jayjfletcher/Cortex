<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use JayI\Cortex\Http\Request;
use JayI\Cortex\Models\Prompt;

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
}
