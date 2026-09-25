<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\ListToolDescriptionVersionsAction;
use JayI\Cortex\Http\Resources\ToolDescriptionVersionResource;
use JayI\Cortex\Models\ToolDescriptionVersion;

final class IndexToolDescriptionVersionsRequest extends ToolDescriptionRequest
{
    public function authorize(): bool
    {
        return $this->allows('viewAny', ToolDescriptionVersion::class, [$this->description()]);
    }

    public function persist(): JsonResponse
    {
        $versions = app(ListToolDescriptionVersionsAction::class)->execute($this->description());

        return ToolDescriptionVersionResource::collection($versions)->response();
    }
}
