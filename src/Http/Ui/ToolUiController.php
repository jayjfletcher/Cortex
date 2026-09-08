<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Ui;

use Illuminate\Contracts\View\View;
use JayI\Cortex\Actions\ListToolsAction;

final class ToolUiController
{
    public function index(): View
    {
        /** @var view-string $view */
        $view = 'cortex::ui.tools.index';

        return view($view, ['tools' => app(ListToolsAction::class)->execute()]);
    }
}
