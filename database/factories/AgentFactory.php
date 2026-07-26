<?php

declare(strict_types=1);

namespace JayI\Cortex\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use JayI\Cortex\Models\Agent;

/**
 * @extends Factory<Agent>
 */
final class AgentFactory extends Factory
{
    protected $model = Agent::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $name = fake()->unique()->sentence(2),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'provider' => null,
            'model' => null,
            'settings' => null,
            'tools' => [],
        ];
    }
}
