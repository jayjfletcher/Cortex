<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use JayI\Cortex\Http\Request;
use JayI\Cortex\Models\ToolDescription;
use JayI\Cortex\Tools\ToolRegistry;

abstract class ToolDescriptionRequest extends Request
{
    /**
     * The registered tool name from the route, verified against the registry.
     */
    protected function tool(): string
    {
        $tool = $this->route('tool');

        if (! is_string($tool) || ! app(ToolRegistry::class)->has($tool)) {
            abort(404);
        }

        return $tool;
    }

    protected function description(): ToolDescription
    {
        $description = ToolDescription::query()->where('tool', $this->tool())->first();

        if ($description === null) {
            abort(404);
        }

        return $description;
    }
}
