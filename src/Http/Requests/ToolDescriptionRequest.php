<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use JayI\Cortex\Http\Request;
use JayI\Cortex\Models\ToolDescription;
use JayI\Cortex\Models\ToolDescriptionVersion;
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

    /**
     * The version named in the route.
     */
    protected function version(): ToolDescriptionVersion
    {
        /** @var ToolDescriptionVersion */
        return $this->description()->versions()->where('version', (int) $this->route('version'))->firstOrFail();
    }

    /**
     * The override for the tool, or an unsaved one when no version exists yet, so
     * creating the first version is checked against the same policy.
     */
    protected function descriptionOrNew(): ToolDescription
    {
        return ToolDescription::query()->firstOrNew(['tool' => $this->tool()]);
    }
}
