<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JayI\Cortex\Models\Agent;

/**
 * @mixin Agent
 */
final class AgentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'provider' => $this->provider,
            'model' => $this->model,
            'settings' => $this->settings,
            'tools' => $this->tools,
            'prompt' => $this->whenLoaded('prompt', fn (): ?string => $this->prompt?->slug),
            'prompt_version' => $this->whenLoaded('pinnedVersion', fn (): ?int => $this->pinnedVersion?->version),
            'sub_agents' => $this->whenLoaded(
                'subAgents',
                fn (): array => $this->subAgents->pluck('slug')->values()->all(),
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
