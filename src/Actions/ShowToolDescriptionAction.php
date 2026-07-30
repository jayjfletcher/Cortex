<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

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
        return ToolDescription::query()
            ->where('tool', $tool)
            ->with('publishedVersion')
            ->firstOrFail();
    }
}
