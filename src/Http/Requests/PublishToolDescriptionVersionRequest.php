<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\PublishToolDescriptionVersionAction;
use JayI\Cortex\Http\Resources\ToolDescriptionResource;

final class PublishToolDescriptionVersionRequest extends ToolDescriptionRequest
{
    public function persist(): JsonResponse
    {
        $description = app(PublishToolDescriptionVersionAction::class)->execute(
            $this->description(),
            (int) $this->route('version'),
        );

        return (new ToolDescriptionResource($description))->response();
    }
}
