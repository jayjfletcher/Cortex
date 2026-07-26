<?php

declare(strict_types=1);

namespace JayI\Cortex\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use JayI\Cortex\Models\Prompt;

/**
 * @extends Factory<Prompt>
 */
final class PromptFactory extends Factory
{
    protected $model = Prompt::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $name = fake()->unique()->sentence(3),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
        ];
    }
}
