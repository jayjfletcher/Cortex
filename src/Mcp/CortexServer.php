<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp;

use JayI\Cortex\Mcp\Tools\CreateAgentTool;
use JayI\Cortex\Mcp\Tools\CreatePromptTool;
use JayI\Cortex\Mcp\Tools\CreatePromptVersionTool;
use JayI\Cortex\Mcp\Tools\DeleteAgentTool;
use JayI\Cortex\Mcp\Tools\DeletePromptTool;
use JayI\Cortex\Mcp\Tools\ListAgentsTool;
use JayI\Cortex\Mcp\Tools\ListPromptsTool;
use JayI\Cortex\Mcp\Tools\ListPromptVersionsTool;
use JayI\Cortex\Mcp\Tools\ListToolsTool;
use JayI\Cortex\Mcp\Tools\PublishPromptVersionTool;
use JayI\Cortex\Mcp\Tools\RunAgentTool;
use JayI\Cortex\Mcp\Tools\ShowAgentTool;
use JayI\Cortex\Mcp\Tools\ShowPromptTool;
use JayI\Cortex\Mcp\Tools\ShowPromptVersionTool;
use JayI\Cortex\Mcp\Tools\UpdateAgentTool;
use JayI\Cortex\Mcp\Tools\UpdatePromptTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Tool;

#[Name('Cortex')]
#[Version('1.0.0')]
#[Instructions('Manage Cortex prompts, agents, and tools. Prompts hold versioned instructions: content is immutable per version, and agents follow the published version unless pinned. Agents combine a prompt, registered tools, and optional sub-agents, and can be executed with run-agent.')]
final class CortexServer extends Server
{
    /**
     * @var array<int, class-string<Tool>|Tool>
     */
    protected array $tools = [
        // Prompts
        ListPromptsTool::class,
        CreatePromptTool::class,
        ShowPromptTool::class,
        UpdatePromptTool::class,
        DeletePromptTool::class,

        // Prompt versions
        ListPromptVersionsTool::class,
        CreatePromptVersionTool::class,
        ShowPromptVersionTool::class,
        PublishPromptVersionTool::class,

        // Agents
        ListAgentsTool::class,
        CreateAgentTool::class,
        ShowAgentTool::class,
        UpdateAgentTool::class,
        DeleteAgentTool::class,

        // Tools + execution
        ListToolsTool::class,
        RunAgentTool::class,
    ];
}
