<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Tools\Concerns;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;

trait DescribesAgentPayload
{
    /**
     * Schema for the agent fields shared by the create and update tools.
     *
     * @return array<string, Type>
     */
    private function agentPayloadSchema(JsonSchema $schema): array
    {
        return [
            'description' => $schema->string()->description('Optional description of the agent\'s purpose.'),
            'provider' => $schema->string()->description('AI provider name (e.g. anthropic, openai). Defaults to the app\'s ai.default config.'),
            'model' => $schema->string()->description('Model identifier for the provider.'),
            'settings' => $schema->object([
                'temperature' => $schema->number()->description('Sampling temperature (0-2).'),
                'max_steps' => $schema->integer()->description('Maximum agentic tool-use steps.'),
                'max_tokens' => $schema->integer()->description('Maximum output tokens.'),
                'top_p' => $schema->number()->description('Nucleus sampling threshold (0-1).'),
            ])->description('Generation settings.'),
            'tools' => $schema->array()->items($schema->string())
                ->description('Registered tool names available to the agent. Replaces the whole list.'),
            'prompt' => $schema->string()->description('Slug of the prompt providing the agent\'s instructions.'),
            'prompt_version' => $schema->integer()->description('Pin a specific prompt version. Omit to follow the published version.')->min(1),
            'sub_agents' => $schema->array()->items($schema->string())
                ->description('Slugs of agents to delegate to as sub-agents. Replaces the whole list.'),
        ];
    }
}
