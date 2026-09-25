<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use JayI\Cortex\Events\Action\AgentRanActionEvent;
use JayI\Cortex\Events\Action\AgentRunningActionEvent;
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
        AgentRunningActionEvent::dispatch($agent, $input);

        $result = $this->perform($agent, $input);

        AgentRanActionEvent::dispatch($agent, $input, $result);

        return $result;
    }

    private function perform(Agent $agent, string $input): AgentResponse
    {
        return $this->factory->make($agent)->prompt($input);
    }
}
