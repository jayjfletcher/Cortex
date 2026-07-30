<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\ShowToolDescriptionAction;
use JayI\Cortex\Http\Resources\ToolDescriptionResource;

final class ShowToolDescriptionRequest extends ToolDescriptionRequest
{
    public function persist(): JsonResponse
    {
        $description = app(ShowToolDescriptionAction::class)->execute($this->tool());

        return (new ToolDescriptionResource($description))->response();
    }
}
