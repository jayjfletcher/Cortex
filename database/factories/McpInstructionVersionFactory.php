<?php

declare(strict_types=1);

namespace JayI\Cortex\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use JayI\Cortex\Models\McpInstruction;
use JayI\Cortex\Models\McpInstructionVersion;

/**
 * @extends Factory<McpInstructionVersion>
 */
final class McpInstructionVersionFactory extends Factory
{
    protected $model = McpInstructionVersion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mcp_instruction_id' => McpInstruction::factory(),
            'version' => 1,
            'content' => fake()->paragraph(),
        ];
    }
}
