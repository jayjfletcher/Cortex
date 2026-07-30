<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\Response;
use JayI\Cortex\Actions\DeleteToolDescriptionAction;

final class DeleteToolDescriptionRequest extends ToolDescriptionRequest
{
    public function persist(): Response
    {
        app(DeleteToolDescriptionAction::class)->execute($this->description());

        return response()->noContent();
    }
}
