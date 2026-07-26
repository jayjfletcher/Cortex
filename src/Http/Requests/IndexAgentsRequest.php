<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\ListAgentsAction;
use JayI\Cortex\Http\Request;
use JayI\Cortex\Http\Resources\AgentResource;

final class IndexAgentsRequest extends Request
{
    public function rules(): array
    {
        return ListAgentsAction::rules();
    }

    public function persist(): JsonResponse
    {
        $agents = app(ListAgentsAction::class)->execute();

        return AgentResource::collection($agents)->response();
    }
}
