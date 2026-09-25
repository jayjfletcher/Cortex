<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use JayI\Cortex\Events\Action\ToolsListedActionEvent;
use JayI\Cortex\Events\Action\ToolsListingActionEvent;
use JayI\Cortex\Tools\ToolRegistry;
use Laravel\Ai\Contracts\Tool;
use Laravel\Mcp\Server\Tool as McpTool;

final class ListToolsAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function __construct(private readonly ToolRegistry $registry) {}

    /**
     * @return list<array{name: string, class: class-string<Tool>|class-string<McpTool>, description: string, schema: array<string, mixed>}>
     */
    public function execute(): array
    {
        ToolsListingActionEvent::dispatch();

        $result = $this->perform();

        ToolsListedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @return list<array{name: string, class: class-string<Tool>|class-string<McpTool>, description: string, schema: array<string, mixed>}>
     */
    private function perform(): array
    {
        return $this->registry->all();
    }
}
