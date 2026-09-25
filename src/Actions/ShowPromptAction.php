<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use JayI\Cortex\Events\Action\PromptShowingActionEvent;
use JayI\Cortex\Events\Action\PromptShownActionEvent;
use JayI\Cortex\Models\Prompt;

final class ShowPromptAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(Prompt $prompt): Prompt
    {
        PromptShowingActionEvent::dispatch($prompt);

        $result = $this->perform($prompt);

        PromptShownActionEvent::dispatch($result);

        return $result;
    }

    private function perform(Prompt $prompt): Prompt
    {
        return $prompt->load('publishedVersion');
    }
}
