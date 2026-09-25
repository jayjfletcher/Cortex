<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use Illuminate\Validation\ValidationException;
use JayI\Cortex\Events\Action\PromptDeletedActionEvent;
use JayI\Cortex\Events\Action\PromptDeletingActionEvent;
use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Support\PublicationCache;

final class DeletePromptAction
{
    public function __construct(private readonly PublicationCache $cache) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(Prompt $prompt): void
    {
        PromptDeletingActionEvent::dispatch($prompt);

        $this->perform($prompt);

        PromptDeletedActionEvent::dispatch($prompt);
    }

    private function perform(Prompt $prompt): void
    {
        if ($prompt->agents()->exists()) {
            throw ValidationException::withMessages([
                'prompt' => 'The prompt is attached to one or more agents and cannot be deleted.',
            ]);
        }

        $prompt->delete();

        $this->cache->forget($this->cache->promptKey((string) $prompt->getKey()));
    }
}
