<?php

declare(strict_types=1);

namespace JayI\Cortex;

use JayI\Cortex\Models\Agent;
use JayI\Cortex\Runtime\AgentFactory;
use JayI\Cortex\Runtime\DbAgent;
use JayI\Cortex\Tools\ToolRegistry;
use Laravel\Ai\Responses\AgentResponse;

class Cortex
{
    public function __construct(
        private readonly ToolRegistry $tools,
        private readonly AgentFactory $agents,
    ) {}

    public function tools(): ToolRegistry
    {
        return $this->tools;
    }

    public function agent(string $slug): DbAgent
    {
        return $this->agents->make(
            Agent::query()->where('slug', $slug)->firstOrFail(),
        );
    }

    public function run(string $slug, string $input): AgentResponse
    {
        return $this->agent($slug)->prompt($input);
    }
}
