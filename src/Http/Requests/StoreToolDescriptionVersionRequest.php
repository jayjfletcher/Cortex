<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\CreateToolDescriptionVersionAction;
use JayI\Cortex\Http\Resources\ToolDescriptionVersionResource;

final class StoreToolDescriptionVersionRequest extends ToolDescriptionRequest
{
    public function rules(): array
    {
        return CreateToolDescriptionVersionAction::rules();
    }

    public function persist(): JsonResponse
    {
        $version = app(CreateToolDescriptionVersionAction::class)->execute($this->tool(), $this->validated());

        return (new ToolDescriptionVersionResource($version))->response()->setStatusCode(201);
    }
}
