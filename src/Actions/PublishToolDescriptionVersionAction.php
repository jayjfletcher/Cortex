<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use JayI\Cortex\Models\ToolDescription;
use JayI\Cortex\Models\ToolDescriptionVersion;
use JayI\Cortex\Support\PublicationCache;

final class PublishToolDescriptionVersionAction
{
    public function __construct(private readonly PublicationCache $cache) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(ToolDescription $description, int $version): ToolDescription
    {
        /** @var ToolDescriptionVersion $descriptionVersion */
        $descriptionVersion = $description->versions()->where('version', $version)->firstOrFail();

        $description->published_version_id = $descriptionVersion->getKey();
        $description->save();

        $this->cache->forget($this->cache->toolDescriptionsKey());

        return $description->load('publishedVersion');
    }
}
