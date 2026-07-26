<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\ListToolsAction;
use JayI\Cortex\Http\Request;
use JayI\Cortex\Http\Resources\ToolResource;

final class IndexToolsRequest extends Request
{
    public function rules(): array
    {
        return ListToolsAction::rules();
    }

    public function persist(): JsonResponse
    {
        $tools = app(ListToolsAction::class)->execute();

        return ToolResource::collection($tools)->response();
    }
}
