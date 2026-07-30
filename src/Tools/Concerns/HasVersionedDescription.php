<?php

declare(strict_types=1);

namespace JayI\Cortex\Tools\Concerns;

use JayI\Cortex\Tools\Tool;
use JayI\Cortex\Tools\ToolDescriptionOverrides;

/**
 * Serve the published Cortex description override, when one exists, in place
 * of the code-declared description. Falls back to whatever the tool declares
 * in code (property, attribute, or class-name default) when no version is
 * published.
 *
 * Use directly on tools that cannot extend {@see Tool}.
 */
trait HasVersionedDescription
{
    public function description(): string
    {
        return app(ToolDescriptionOverrides::class)->for($this->name())
            ?? parent::description();
    }
}
