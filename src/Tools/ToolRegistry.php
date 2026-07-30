<?php

declare(strict_types=1);

namespace JayI\Cortex\Tools;

use Illuminate\Contracts\Container\Container;
use Illuminate\JsonSchema\JsonSchema;
use InvalidArgumentException;
use JayI\Cortex\Exceptions\ToolNotFoundException;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\McpServerTool;
use Laravel\Ai\Tools\ToolNameResolver;
use Laravel\Mcp\Server\Tool as McpTool;

final class ToolRegistry
{
    /**
     * @var array<string, class-string<Tool>|class-string<McpTool>>
     */
    private array $tools = [];

    private bool $configLoaded = false;

    public function __construct(private readonly Container $container) {}

    public function register(string $name, string $class): void
    {
        if (! is_a($class, Tool::class, true) && ! is_a($class, McpTool::class, true)) {
            throw new InvalidArgumentException(
                sprintf('Tool [%s] must implement %s or extend %s.', $class, Tool::class, McpTool::class),
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

        $tool = $this->container->make($this->tools[$name]);

        if (! $tool instanceof Tool) {
            $tool = new McpServerTool($tool);
        }

        $override = $this->container->make(ToolDescriptionOverrides::class)->for($name);

        return $override === null ? $tool : new DescribedTool($tool, $override);
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
     * @return list<array{name: string, class: class-string<Tool>|class-string<McpTool>, description: string, schema: array<string, mixed>}>
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

        /** @var array<string|int, string> $configured */
        $configured = $this->container->make('config')->get('cortex.tools', []);

        foreach ($configured as $name => $class) {
            $this->register(is_string($name) ? $name : $this->deriveName($class), $class);
        }
    }

    /**
     * Resolve a registration name for a list-style (unkeyed) config entry
     * from the tool's own name. Non-tool classes fall through so that
     * register() rejects them with its usual exception.
     */
    private function deriveName(string $class): string
    {
        if (! is_a($class, Tool::class, true) && ! is_a($class, McpTool::class, true)) {
            return $class;
        }

        $tool = $this->container->make($class);

        if ($tool instanceof McpTool) {
            return $tool->name();
        }

        return $tool instanceof Tool ? ToolNameResolver::resolve($tool) : $class;
    }
}
