<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use JayI\Cortex\Events\Action\ToolDescriptionShowingActionEvent;
use JayI\Cortex\Events\Action\ToolDescriptionShownActionEvent;
use JayI\Cortex\Models\ToolDescription;

final class ShowToolDescriptionAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(string $tool): ToolDescription
    {
        ToolDescriptionShowingActionEvent::dispatch($tool);

        $result = $this->perform($tool);

        ToolDescriptionShownActionEvent::dispatch($result);

        return $result;
    }

    private function perform(string $tool): ToolDescription
    {
        return ToolDescription::query()
            ->where('tool', $tool)
            ->with('publishedVersion')
            ->firstOrFail();
    }
}
