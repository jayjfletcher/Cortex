<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use JayI\Cortex\Actions\ListToolsAction;
use JayI\Cortex\Http\Resources\ToolResource;
use JayI\Cortex\Mcp\Request;
use Laravel\Mcp\ResponseFactory;

final class ListToolsMcpRequest extends Request
{
    protected function handle(array $validated): ResponseFactory
    {
        $tools = app(ListToolsAction::class)->execute();

        return $this->structuredCollection(ToolResource::collection($tools)->resolve());
    }
}
