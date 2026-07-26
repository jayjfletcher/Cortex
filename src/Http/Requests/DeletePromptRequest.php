<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\Response;
use JayI\Cortex\Actions\DeletePromptAction;

final class DeletePromptRequest extends PromptRequest
{
    public function persist(): Response
    {
        app(DeletePromptAction::class)->execute($this->prompt());

        return response()->noContent();
    }
}
