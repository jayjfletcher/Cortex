<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use Illuminate\Database\Eloquent\Collection;
use JayI\Cortex\Events\Action\ToolDescriptionVersionsListedActionEvent;
use JayI\Cortex\Events\Action\ToolDescriptionVersionsListingActionEvent;
use JayI\Cortex\Models\ToolDescription;
use JayI\Cortex\Models\ToolDescriptionVersion;

final class ListToolDescriptionVersionsAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    /**
     * @return Collection<int, ToolDescriptionVersion>
     */
    public function execute(ToolDescription $description): Collection
    {
        ToolDescriptionVersionsListingActionEvent::dispatch($description);

        $result = $this->perform($description);

        ToolDescriptionVersionsListedActionEvent::dispatch($description, $result);

        return $result;
    }

    /**
     * @return Collection<int, ToolDescriptionVersion>
     */
    private function perform(ToolDescription $description): Collection
    {
        return $description->versions()->orderByDesc('version')->get();
    }
}
