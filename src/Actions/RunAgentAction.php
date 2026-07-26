<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use JayI\Cortex\Models\Agent;
use JayI\Cortex\Runtime\AgentFactory;
use Laravel\Ai\Responses\AgentResponse;

final class RunAgentAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'input' => ['required', 'string'],
        ];
    }

    public function __construct(private readonly AgentFactory $factory) {}

    public function execute(Agent $agent, string $input): AgentResponse
    {
        return $this->factory->make($agent)->prompt($input);
    }
}
