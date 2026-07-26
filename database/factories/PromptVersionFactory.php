<?php

declare(strict_types=1);

namespace JayI\Cortex\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Models\PromptVersion;

/**
 * @extends Factory<PromptVersion>
 */
final class PromptVersionFactory extends Factory
{
    protected $model = PromptVersion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'prompt_id' => Prompt::factory(),
            'version' => 1,
            'content' => fake()->paragraph(),
        ];
    }
}
