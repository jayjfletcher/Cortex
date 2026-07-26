<?php

declare(strict_types=1);

namespace JayI\Cortex\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use JayI\Cortex\Database\Factories\PromptVersionFactory;
use LogicException;

/**
 * @property string $id
 * @property string $prompt_id
 * @property int $version
 * @property string $content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
final class PromptVersion extends Model
{
    /** @use HasFactory<PromptVersionFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'cortex_prompt_versions';

    protected $fillable = [
        'version',
        'content',
    ];

    protected static function booted(): void
    {
        self::updating(function (): never {
            throw new LogicException('Prompt versions are immutable. Create a new version instead.');
        });
    }

    /**
     * @return BelongsTo<Prompt, $this>
     */
    public function prompt(): BelongsTo
    {
        return $this->belongsTo(Prompt::class, 'prompt_id');
    }

    protected static function newFactory(): PromptVersionFactory
    {
        return PromptVersionFactory::new();
    }
}
