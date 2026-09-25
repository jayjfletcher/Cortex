<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use JayI\Cortex\Events\Action\AgentsListedActionEvent;
use JayI\Cortex\Events\Action\AgentsListingActionEvent;
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
        AgentsListingActionEvent::dispatch($page);

        $result = $this->perform($page);

        AgentsListedActionEvent::dispatch($result);

        return $result;
    }

    /**
     * @return LengthAwarePaginator<int, Agent>
     */
    private function perform(?int $page = null): LengthAwarePaginator
    {
        return Agent::query()
            ->with(['prompt', 'pinnedVersion', 'subAgents'])
            ->orderBy('name')
            ->paginate(page: $page);
    }
}
