<?php

declare(strict_types=1);

namespace JayI\Cortex\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use JayI\Cortex\Database\Factories\PromptFactory;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $published_version_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class Prompt extends Model
{
    /** @use HasFactory<PromptFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'cortex_prompts';

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * @return HasMany<PromptVersion, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(PromptVersion::class, 'prompt_id');
    }

    /**
     * @return BelongsTo<PromptVersion, $this>
     */
    public function publishedVersion(): BelongsTo
    {
        return $this->belongsTo(PromptVersion::class, 'published_version_id');
    }

    /**
     * @return HasMany<Agent, $this>
     */
    public function agents(): HasMany
    {
        return $this->hasMany(Agent::class, 'prompt_id');
    }

    protected static function newFactory(): PromptFactory
    {
        return PromptFactory::new();
    }
}
