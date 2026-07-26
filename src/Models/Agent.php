<?php

declare(strict_types=1);

namespace JayI\Cortex\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use JayI\Cortex\Database\Factories\AgentFactory;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $prompt_id
 * @property string|null $prompt_version_id
 * @property string|null $provider
 * @property string|null $model
 * @property array<string, mixed>|null $settings
 * @property list<string> $tools
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class Agent extends Model
{
    /** @use HasFactory<AgentFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'cortex_agents';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'prompt_id',
        'prompt_version_id',
        'provider',
        'model',
        'settings',
        'tools',
    ];

    protected $attributes = [
        'tools' => '[]',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'tools' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Prompt, $this>
     */
    public function prompt(): BelongsTo
    {
        return $this->belongsTo(Prompt::class, 'prompt_id');
    }

    /**
     * @return BelongsTo<PromptVersion, $this>
     */
    public function pinnedVersion(): BelongsTo
    {
        return $this->belongsTo(PromptVersion::class, 'prompt_version_id');
    }

    /**
     * @return BelongsToMany<Agent, $this>
     */
    public function subAgents(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'cortex_agent_agent', 'agent_id', 'sub_agent_id');
    }

    /**
     * @return BelongsToMany<Agent, $this>
     */
    public function parentAgents(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'cortex_agent_agent', 'sub_agent_id', 'agent_id');
    }

    protected static function newFactory(): AgentFactory
    {
        return AgentFactory::new();
    }
}
