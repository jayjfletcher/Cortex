<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use JayI\Cortex\Actions\Concerns\ResolvesAgentReferences;
use JayI\Cortex\Models\Agent;
use JayI\Cortex\Tools\ToolRegistry;

final class CreateAgentAction
{
    use ResolvesAgentReferences;

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:cortex_agents,slug'],
            'description' => ['nullable', 'string'],
            'provider' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'settings' => ['nullable', 'array:temperature,max_steps,max_tokens,top_p'],
            'settings.temperature' => ['sometimes', 'numeric', 'between:0,2'],
            'settings.max_steps' => ['sometimes', 'integer', 'min:1'],
            'settings.max_tokens' => ['sometimes', 'integer', 'min:1'],
            'settings.top_p' => ['sometimes', 'numeric', 'between:0,1'],
            'tools' => ['sometimes', 'array'],
            'tools.*' => ['string', Rule::in(app(ToolRegistry::class)->names())],
            'prompt' => ['nullable', 'string', Rule::exists('cortex_prompts', 'slug')],
            'prompt_version' => ['nullable', 'integer', 'min:1'],
            'sub_agents' => ['sometimes', 'array'],
            'sub_agents.*' => ['string', 'distinct', Rule::exists('cortex_agents', 'slug')],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Agent
    {
        return DB::transaction(function () use ($data): Agent {
            $agent = Agent::query()->create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
                'provider' => $data['provider'] ?? null,
                'model' => $data['model'] ?? null,
                'settings' => $data['settings'] ?? null,
                'tools' => $data['tools'] ?? [],
                ...$this->promptAttributes($data),
            ]);

            $agent->subAgents()->sync($this->subAgentIds($data['sub_agents'] ?? []));

            return $agent->load(['prompt', 'pinnedVersion', 'subAgents']);
        });
    }
}
