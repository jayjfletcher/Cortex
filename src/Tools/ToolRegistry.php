<?php

declare(strict_types=1);

namespace JayI\Cortex\Tools;

use Illuminate\Contracts\Container\Container;
use Illuminate\JsonSchema\JsonSchema;
use InvalidArgumentException;
use JayI\Cortex\Exceptions\ToolNotFoundException;
use Laravel\Ai\Contracts\Tool;

final class ToolRegistry
{
    /**
     * @var array<string, class-string<Tool>>
     */
    private array $tools = [];

    private bool $configLoaded = false;

    public function __construct(private readonly Container $container) {}

    public function register(string $name, string $class): void
    {
        if (! is_a($class, Tool::class, true)) {
            throw new InvalidArgumentException(
                sprintf('Tool [%s] must implement %s.', $class, Tool::class),
            );
        }

        $this->loadConfigTools();

        $this->tools[$name] = $class;
    }

    public function has(string $name): bool
    {
        $this->loadConfigTools();

        return array_key_exists($name, $this->tools);
    }

    public function get(string $name): Tool
    {
        if (! $this->has($name)) {
            throw ToolNotFoundException::forName($name);
        }

        return $this->container->make($this->tools[$name]);
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        $this->loadConfigTools();

        return array_keys($this->tools);
    }

    /**
     * @return list<array{name: string, class: class-string<Tool>, description: string, schema: array<string, mixed>}>
     */
    public function all(): array
    {
        $this->loadConfigTools();

        return array_map(function (string $name): array {
            $tool = $this->get($name);

            return [
                'name' => $name,
                'class' => $this->tools[$name],
                'description' => (string) $tool->description(),
                'schema' => JsonSchema::object($tool->schema(...))->toArray(),
            ];
        }, $this->names());
    }

    private function loadConfigTools(): void
    {
        if ($this->configLoaded) {
            return;
        }

        $this->configLoaded = true;

        /** @var array<string, class-string<Tool>> $configured */
        $configured = $this->container->make('config')->get('cortex.tools', []);

        foreach ($configured as $name => $class) {
            $this->register($name, $class);
        }
    }
}
