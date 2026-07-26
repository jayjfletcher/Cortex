<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use JayI\Cortex\Models\Agent;
use JayI\Cortex\Models\Prompt;

trait ResolvesAgentReferences
{
    /**
     * Convert prompt slug and version number inputs into foreign key attributes.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, string|null>
     */
    private function promptAttributes(array $data, ?Agent $existing = null): array
    {
        if (! array_key_exists('prompt', $data) && ! array_key_exists('prompt_version', $data)) {
            return [];
        }

        if (array_key_exists('prompt', $data)) {
            $prompt = is_string($data['prompt'])
                ? Prompt::query()->where('slug', $data['prompt'])->firstOrFail()
                : null;
        } else {
            $prompt = $existing?->prompt;
        }

        if ($prompt === null) {
            if (($data['prompt_version'] ?? null) !== null) {
                throw ValidationException::withMessages([
                    'prompt_version' => 'A prompt version cannot be pinned without a prompt.',
                ]);
            }

            return ['prompt_id' => null, 'prompt_version_id' => null];
        }

        $attributes = ['prompt_id' => (string) $prompt->getKey()];

        if (array_key_exists('prompt_version', $data)) {
            $attributes['prompt_version_id'] = $data['prompt_version'] === null
                ? null
                : (string) $prompt->versions()->where('version', $data['prompt_version'])->firstOrFail()->getKey();
        } else {
            $attributes['prompt_version_id'] = null;
        }

        return $attributes;
    }

    /**
     * @param  list<string>  $slugs
     * @return list<string>
     */
    private function subAgentIds(array $slugs): array
    {
        if ($slugs === []) {
            return [];
        }

        return array_values(array_map(
            fn (int|string $id): string => (string) $id,
            Agent::query()->whereIn('slug', $slugs)->pluck('id')->all(),
        ));
    }

    /**
     * Reject sub-agent selections that would create a cycle back to the agent.
     *
     * @param  list<string>  $subAgentIds
     */
    private function assertNoCycles(Agent $agent, array $subAgentIds): void
    {
        $queue = $subAgentIds;
        $seen = [];

        while ($queue !== []) {
            $id = array_shift($queue);

            if (in_array($id, $seen, true)) {
                continue;
            }

            $seen[] = $id;

            if ($id === (string) $agent->getKey()) {
                throw ValidationException::withMessages([
                    'sub_agents' => 'The selected sub-agents would create a circular reference.',
                ]);
            }

            foreach (DB::table('cortex_agent_agent')->where('agent_id', $id)->pluck('sub_agent_id') as $subId) {
                $queue[] = (string) $subId;
            }
        }
    }
}
