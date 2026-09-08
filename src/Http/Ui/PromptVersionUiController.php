<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Ui;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use JayI\Cortex\Actions\CreatePromptVersionAction;
use JayI\Cortex\Actions\PublishPromptVersionAction;
use JayI\Cortex\Models\Prompt;

final class PromptVersionUiController
{
    public function store(Request $request, Prompt $prompt): RedirectResponse
    {
        $data = $request->validate(CreatePromptVersionAction::rules());

        app(CreatePromptVersionAction::class)->execute($prompt, $data);

        return redirect()
            ->route('atrium.cortex.prompts.show', $prompt->slug)
            ->with('status', __('cortex::cortex.version_created'));
    }

    public function publish(Prompt $prompt, int $version): RedirectResponse
    {
        app(PublishPromptVersionAction::class)->execute($prompt, $version);

        return redirect()
            ->route('atrium.cortex.prompts.show', $prompt->slug)
            ->with('status', __('cortex::cortex.version_published'));
    }
}
