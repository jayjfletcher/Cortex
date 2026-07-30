<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use Illuminate\Database\Eloquent\Collection;
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
        return $description->versions()->orderByDesc('version')->get();
    }
}
