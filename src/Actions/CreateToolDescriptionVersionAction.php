<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use Illuminate\Support\Facades\DB;
use JayI\Cortex\Models\ToolDescription;
use JayI\Cortex\Models\ToolDescriptionVersion;
use JayI\Cortex\Support\PublicationCache;

final class CreateToolDescriptionVersionAction
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
    public function execute(string $tool, array $data): ToolDescriptionVersion
    {
        return DB::transaction(function () use ($tool, $data): ToolDescriptionVersion {
            /** @var ToolDescription $description */
            $description = ToolDescription::query()->firstOrCreate(['tool' => $tool]);

            /** @var ToolDescriptionVersion $version */
            $version = $description->versions()->create([
                'version' => ((int) $description->versions()->lockForUpdate()->max('version')) + 1,
                'content' => $data['content'],
            ]);

            if ($data['publish'] ?? false) {
                $description->published_version_id = $version->getKey();
                $description->save();

                $this->cache->forget($this->cache->toolDescriptionsKey());
            }

            return $version;
        });
    }
}
