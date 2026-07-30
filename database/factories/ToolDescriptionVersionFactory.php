<?php

declare(strict_types=1);

namespace JayI\Cortex\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JayI\Cortex\Models\ToolDescription;
use JayI\Cortex\Models\ToolDescriptionVersion;

/**
 * @extends Factory<ToolDescriptionVersion>
 */
final class ToolDescriptionVersionFactory extends Factory
{
    protected $model = ToolDescriptionVersion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tool_description_id' => ToolDescription::factory(),
            'version' => 1,
            'content' => fake()->paragraph(),
        ];
    }
}
