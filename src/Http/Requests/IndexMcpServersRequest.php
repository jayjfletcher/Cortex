<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Requests;

use Illuminate\Http\JsonResponse;
use JayI\Cortex\Actions\ListMcpServersAction;
use JayI\Cortex\Http\Request;
use JayI\Cortex\Http\Resources\McpServerResource;

final class IndexMcpServersRequest extends Request
{
    public function rules(): array
    {
        return ListMcpServersAction::rules();
    }

    public function persist(): JsonResponse
    {
        $servers = app(ListMcpServersAction::class)->execute();

        return McpServerResource::collection($servers)->response();
    }
}
