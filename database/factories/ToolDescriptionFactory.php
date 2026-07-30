<?php

declare(strict_types=1);

namespace JayI\Cortex\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JayI\Cortex\Models\ToolDescription;

/**
 * @extends Factory<ToolDescription>
 */
final class ToolDescriptionFactory extends Factory
{
    protected $model = ToolDescription::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tool' => fake()->unique()->slug(3),
        ];
    }
}
