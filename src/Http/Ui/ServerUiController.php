<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Ui;

use Illuminate\Contracts\View\View;
use JayI\Cortex\Actions\ListMcpServersAction;

final class ServerUiController
{
    public function index(): View
    {
        /** @var view-string $view */
        $view = 'cortex::ui.servers.index';

        return view($view, ['servers' => app(ListMcpServersAction::class)->execute()]);
    }
}
