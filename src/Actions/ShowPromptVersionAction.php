<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Models\PromptVersion;

final class ShowPromptVersionAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(Prompt $prompt, int $version): PromptVersion
    {
        /** @var PromptVersion */
        return $prompt->versions()->where('version', $version)->firstOrFail();
    }
}
