<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\ListToolDescriptionVersionsAction;
use JayI\Cortex\Http\Resources\ToolDescriptionVersionResource;

final class IndexToolDescriptionVersionsRequest extends ToolDescriptionRequest
{
    public function persist(): JsonResponse
    {
        $versions = app(ListToolDescriptionVersionsAction::class)->execute($this->description());

        return ToolDescriptionVersionResource::collection($versions)->response();
    }
}
