<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use JayI\Cortex\Models\McpInstruction;
use JayI\Cortex\Models\McpInstructionVersion;
use JayI\Cortex\Support\PublicationCache;

final class PublishMcpInstructionVersionAction
{
    public function __construct(private readonly PublicationCache $cache) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(McpInstruction $instruction, int $version): McpInstruction
    {
        /** @var McpInstructionVersion $instructionVersion */
        $instructionVersion = $instruction->versions()->where('version', $version)->firstOrFail();

        $instruction->published_version_id = $instructionVersion->getKey();
        $instruction->save();

        $this->cache->forget($this->cache->mcpInstructionsKey());

        return $instruction->load('publishedVersion');
    }
}
