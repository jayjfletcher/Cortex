<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use JayI\Cortex\Actions\Concerns\ResolvesAgentReferences;
use JayI\Cortex\Models\Agent;
use JayI\Cortex\Tools\ToolRegistry;

final class UpdateAgentAction
{
    use ResolvesAgentReferences;

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'provider' => ['sometimes', 'nullable', 'string', 'max:255'],
            'model' => ['sometimes', 'nullable', 'string', 'max:255'],
            'settings' => ['sometimes', 'nullable', 'array:temperature,max_steps,max_tokens,top_p'],
            'settings.temperature' => ['sometimes', 'numeric', 'between:0,2'],
            'settings.max_steps' => ['sometimes', 'integer', 'min:1'],
            'settings.max_tokens' => ['sometimes', 'integer', 'min:1'],
            'settings.top_p' => ['sometimes', 'numeric', 'between:0,1'],
            'tools' => ['sometimes', 'array'],
            'tools.*' => ['string', Rule::in(app(ToolRegistry::class)->names())],
            'prompt' => ['sometimes', 'nullable', 'string', Rule::exists('cortex_prompts', 'slug')],
            'prompt_version' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'sub_agents' => ['sometimes', 'array'],
            'sub_agents.*' => ['string', 'distinct', Rule::exists('cortex_agents', 'slug')],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Agent $agent, array $data): Agent
    {
        return DB::transaction(function () use ($agent, $data): Agent {
            $agent->fill([
                ...collect($data)->only(['name', 'description', 'provider', 'model', 'settings', 'tools'])->all(),
                ...$this->promptAttributes($data, $agent),
            ])->save();

            if (array_key_exists('sub_agents', $data)) {
                /** @var list<string> $slugs */
                $slugs = $data['sub_agents'];

                $subAgentIds = $this->subAgentIds($slugs);

                $this->assertNoCycles($agent, $subAgentIds);

                $agent->subAgents()->sync($subAgentIds);
            }

            return $agent->refresh()->load(['prompt', 'pinnedVersion', 'subAgents']);
        });
    }
}
