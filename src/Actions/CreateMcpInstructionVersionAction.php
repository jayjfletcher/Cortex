<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use Illuminate\Support\Facades\DB;
use JayI\Cortex\Models\McpInstruction;
use JayI\Cortex\Models\McpInstructionVersion;
use JayI\Cortex\Support\PublicationCache;

final class CreateMcpInstructionVersionAction
{
    public function __construct(private readonly PublicationCache $cache) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'content' => ['required', 'string'],
            'publish' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(string $server, array $data): McpInstructionVersion
    {
        return DB::transaction(function () use ($server, $data): McpInstructionVersion {
            /** @var McpInstruction $instruction */
            $instruction = McpInstruction::query()->firstOrCreate(['server' => $server]);

            /** @var McpInstructionVersion $version */
            $version = $instruction->versions()->create([
                'version' => ((int) $instruction->versions()->lockForUpdate()->max('version')) + 1,
                'content' => $data['content'],
            ]);

            if ($data['publish'] ?? false) {
                $instruction->published_version_id = $version->getKey();
                $instruction->save();

                $this->cache->forget($this->cache->mcpInstructionsKey());
            }

            return $version;
        });
    }
}
