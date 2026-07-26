<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use JayI\Cortex\Models\Agent;

final class ListAgentsAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    /**
     * @return LengthAwarePaginator<int, Agent>
     */
    public function execute(?int $page = null): LengthAwarePaginator
    {
        return Agent::query()
            ->with(['prompt', 'pinnedVersion', 'subAgents'])
            ->orderBy('name')
            ->paginate(page: $page);
    }
}
