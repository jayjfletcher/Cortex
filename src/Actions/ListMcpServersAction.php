<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use JayI\Cortex\Mcp\McpServerRegistry;
use Laravel\Mcp\Server as McpServer;

final class ListMcpServersAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function __construct(private readonly McpServerRegistry $registry) {}

    /**
     * @return list<array{name: string, class: class-string<McpServer>, instructions: string}>
     */
    public function execute(): array
    {
        return $this->registry->all();
    }
}
