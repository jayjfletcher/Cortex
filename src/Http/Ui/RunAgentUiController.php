<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Ui;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use JayI\Cortex\Actions\RunAgentAction;
use JayI\Cortex\Models\Agent;

final class RunAgentUiController
{
    public function create(Request $request): View
    {
        /** @var view-string $view */
        $view = 'cortex::ui.run';

        return view($view, [
            'agents' => Agent::query()->orderBy('name')->get(),
            'selected' => $request->string('agent')->toString(),
            'result' => null,
        ]);
    }

    public function store(Request $request): View
    {
        $data = $request->validate([
            'agent' => ['required', 'string', 'exists:cortex_agents,slug'],
            'input' => ['required', 'string'],
        ]);

        /** @var Agent $agent */
        $agent = Agent::query()->where('slug', $data['agent'])->firstOrFail();

        $response = app(RunAgentAction::class)->execute($agent, $data['input']);

        /** @var view-string $view */
        $view = 'cortex::ui.run';

        return view($view, [
            'agents' => Agent::query()->orderBy('name')->get(),
            'selected' => $data['agent'],
            'input' => $data['input'],
            'result' => $response,
        ]);
    }
}
