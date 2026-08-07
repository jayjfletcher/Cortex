<?php

declare(strict_types=1);

namespace JayI\Cortex\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JayI\Cortex\Models\McpInstruction;

/**
 * @extends Factory<McpInstruction>
 */
final class McpInstructionFactory extends Factory
{
    protected $model = McpInstruction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'server' => fake()->unique()->slug(2),
        ];
    }
}
