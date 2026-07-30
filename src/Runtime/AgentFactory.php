<?php

declare(strict_types=1);

namespace JayI\Cortex\Runtime;

use JayI\Cortex\Exceptions\CircularAgentReferenceException;
use JayI\Cortex\Exceptions\PromptNotPublishedException;
use JayI\Cortex\Models\Agent;
use JayI\Cortex\Support\PublicationCache;
use JayI\Cortex\Tools\ToolRegistry;

final class AgentFactory
{
    public function __construct(
        private readonly ToolRegistry $registry,
        private readonly PublicationCache $cache,
    ) {}

    /**
     * @param  list<string>  $visited
     */
    public function make(Agent $agent, array $visited = []): DbAgent
    {
        if (in_array((string) $agent->getKey(), $visited, true)) {
            throw CircularAgentReferenceException::forAgent($agent);
        }

        $visited[] = (string) $agent->getKey();

        $tools = array_map(
            fn (string $name) => $this->registry->get($name),
            $agent->tools,
        );

        foreach ($agent->subAgents as $subAgent) {
            $tools[] = $this->make($subAgent, $visited);
        }

        return new DbAgent(
            agentInstructions: $this->instructions($agent),
            agentTools: $tools,
            agentProvider: $agent->provider,
            agentModel: $agent->model,
            settings: $agent->settings ?? [],
        );
    }

    private function instructions(Agent $agent): string
    {
        if ($agent->prompt === null) {
            return '';
        }

        if ($agent->pinnedVersion !== null) {
            return $agent->pinnedVersion->content;
        }

        $prompt = $agent->prompt;

        // Cached until a new version is published; the publishing actions
        // invalidate the key. Unpublished prompts resolve to null, which is
        // never cached, so publishing takes effect immediately.
        $published = $this->cache->remember(
            $this->cache->promptKey((string) $prompt->getKey()),
            fn (): ?string => $prompt->publishedVersion?->content,
        );

        if (! is_string($published)) {
            throw PromptNotPublishedException::forPrompt($prompt);
        }

        return $published;
    }
}
