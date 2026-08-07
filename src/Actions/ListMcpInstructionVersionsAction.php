<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use Illuminate\Database\Eloquent\Collection;
use JayI\Cortex\Models\McpInstruction;
use JayI\Cortex\Models\McpInstructionVersion;

final class ListMcpInstructionVersionsAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    /**
     * @return Collection<int, McpInstructionVersion>
     */
    public function execute(McpInstruction $instruction): Collection
    {
        return $instruction->versions()->orderByDesc('version')->get();
    }
}
