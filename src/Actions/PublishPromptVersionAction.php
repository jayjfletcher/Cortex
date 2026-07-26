<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Models\PromptVersion;

final class PublishPromptVersionAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(Prompt $prompt, int $version): Prompt
    {
        /** @var PromptVersion $promptVersion */
        $promptVersion = $prompt->versions()->where('version', $version)->firstOrFail();

        $prompt->published_version_id = $promptVersion->getKey();
        $prompt->save();

        return $prompt->load('publishedVersion');
    }
}
