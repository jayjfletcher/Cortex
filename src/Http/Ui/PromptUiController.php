<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Ui;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use JayI\Cortex\Actions\CreatePromptAction;
use JayI\Cortex\Actions\DeletePromptAction;
use JayI\Cortex\Actions\ListPromptsAction;
use JayI\Cortex\Actions\ListPromptVersionsAction;
use JayI\Cortex\Actions\UpdatePromptAction;
use JayI\Cortex\Models\Prompt;

/**
 * Server-rendered prompt management inside the Atrium dashboard.
 *
 * Every write goes through the same Action the JSON API uses, so the two
 * surfaces cannot drift apart.
 */
final class PromptUiController
{
    public function index(Request $request): View
    {
        /** @var view-string $view */
        $view = 'cortex::ui.prompts.index';

        return view($view, [
            'prompts' => app(ListPromptsAction::class)->execute($request->integer('page') ?: null),
        ]);
    }

    public function create(): View
    {
        /** @var view-string $view */
        $view = 'cortex::ui.prompts.form';

        return view($view, ['prompt' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(CreatePromptAction::rules());

        $prompt = app(CreatePromptAction::class)->execute($data);

        return redirect()
            ->route('atrium.cortex.prompts.show', $prompt->slug)
            ->with('status', __('cortex::cortex.prompt_created'));
    }

    public function show(Request $request, Prompt $prompt): View
    {
        /** @var view-string $view */
        $view = 'cortex::ui.prompts.show';

        return view($view, [
            'prompt' => $prompt->load('publishedVersion'),
            'versions' => app(ListPromptVersionsAction::class)->execute($prompt, $request->integer('page') ?: null),
        ]);
    }

    public function edit(Prompt $prompt): View
    {
        /** @var view-string $view */
        $view = 'cortex::ui.prompts.form';

        return view($view, ['prompt' => $prompt]);
    }

    public function update(Request $request, Prompt $prompt): RedirectResponse
    {
        $data = $request->validate(UpdatePromptAction::rules());

        app(UpdatePromptAction::class)->execute($prompt, $data);

        return redirect()
            ->route('atrium.cortex.prompts.show', $prompt->slug)
            ->with('status', __('cortex::cortex.prompt_updated'));
    }

    public function destroy(Prompt $prompt): RedirectResponse
    {
        // Throws a ValidationException when an agent still references it.
        app(DeletePromptAction::class)->execute($prompt);

        return redirect()
            ->route('atrium.cortex.prompts.index')
            ->with('status', __('cortex::cortex.prompt_deleted'));
    }
}
