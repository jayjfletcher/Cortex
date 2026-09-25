<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use JayI\Cortex\Events\Action\PromptVersionPublishedActionEvent;
use JayI\Cortex\Events\Action\PromptVersionPublishingActionEvent;
use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Models\PromptVersion;
use JayI\Cortex\Support\PublicationCache;

final class PublishPromptVersionAction
{
    public function __construct(private readonly PublicationCache $cache) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(Prompt $prompt, int $version): Prompt
    {
        PromptVersionPublishingActionEvent::dispatch($prompt, $version);

        $result = $this->perform($prompt, $version);

        PromptVersionPublishedActionEvent::dispatch($result);

        return $result;
    }

    private function perform(Prompt $prompt, int $version): Prompt
    {
        /** @var PromptVersion $promptVersion */
        $promptVersion = $prompt->versions()->where('version', $version)->firstOrFail();

        $prompt->published_version_id = $promptVersion->getKey();
        $prompt->save();

        $this->cache->forget($this->cache->promptKey((string) $prompt->getKey()));

        return $prompt->load('publishedVersion');
    }
}
