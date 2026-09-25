<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use JayI\Cortex\Events\Action\ToolDescriptionDeletedActionEvent;
use JayI\Cortex\Events\Action\ToolDescriptionDeletingActionEvent;
use JayI\Cortex\Models\ToolDescription;
use JayI\Cortex\Support\PublicationCache;

final class DeleteToolDescriptionAction
{
    public function __construct(private readonly PublicationCache $cache) {}

    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(ToolDescription $description): void
    {
        ToolDescriptionDeletingActionEvent::dispatch($description);

        $this->perform($description);

        ToolDescriptionDeletedActionEvent::dispatch($description);
    }

    private function perform(ToolDescription $description): void
    {
        $description->delete();

        $this->cache->forget($this->cache->toolDescriptionsKey());
    }
}
