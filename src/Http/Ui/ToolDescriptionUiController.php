<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Ui;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use JayI\Cortex\Actions\CreateToolDescriptionVersionAction;
use JayI\Cortex\Actions\DeleteToolDescriptionAction;
use JayI\Cortex\Actions\PublishToolDescriptionVersionAction;
use JayI\Cortex\Models\ToolDescription;
use JayI\Cortex\Tools\ToolRegistry;

final class ToolDescriptionUiController
{
    public function show(string $tool): View
    {
        $this->assertRegistered($tool);

        $description = $this->override($tool);

        /** @var view-string $view */
        $view = 'cortex::ui.tools.description';

        return view($view, [
            'tool' => $tool,
            'codeDescription' => app(ToolRegistry::class)->get($tool)->description(),
            'description' => $description,
            'versions' => $description?->versions()->orderByDesc('version')->get() ?? collect(),
        ]);
    }

    public function store(Request $request, string $tool): RedirectResponse
    {
        $this->assertRegistered($tool);

        $data = $request->validate(CreateToolDescriptionVersionAction::rules());

        app(CreateToolDescriptionVersionAction::class)->execute($tool, $data);

        return redirect()
            ->route('atrium.cortex.tools.description', $tool)
            ->with('status', __('cortex::cortex.version_created'));
    }

    public function publish(string $tool, int $version): RedirectResponse
    {
        $this->assertRegistered($tool);

        $description = $this->override($tool);

        abort_if($description === null, 404);

        app(PublishToolDescriptionVersionAction::class)->execute($description, $version);

        return redirect()
            ->route('atrium.cortex.tools.description', $tool)
            ->with('status', __('cortex::cortex.version_published'));
    }

    public function destroy(string $tool): RedirectResponse
    {
        $this->assertRegistered($tool);

        $description = $this->override($tool);

        abort_if($description === null, 404);

        app(DeleteToolDescriptionAction::class)->execute($description);

        return redirect()
            ->route('atrium.cortex.tools.description', $tool)
            ->with('status', __('cortex::cortex.override_removed'));
    }

    /**
     * The override row, or null when the tool still uses the description it
     * declares in code. The JSON API answers 404 here; a page needs the
     * distinction rather than an error.
     */
    private function override(string $tool): ?ToolDescription
    {
        return ToolDescription::query()
            ->where('tool', $tool)
            ->with('publishedVersion')
            ->first();
    }

    private function assertRegistered(string $tool): void
    {
        abort_unless(app(ToolRegistry::class)->has($tool), 404);
    }
}
