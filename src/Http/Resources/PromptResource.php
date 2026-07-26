<?php

declare(strict_types=1);

namespace JayI\Cortex\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JayI\Cortex\Models\Prompt;

/**
 * @mixin Prompt
 */
final class PromptResource extends JsonResource
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
            'published_version' => new PromptVersionResource($this->whenLoaded('publishedVersion')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
