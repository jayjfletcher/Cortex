<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use JayI\Cortex\Events\Action\PromptVersionsListedActionEvent;
use JayI\Cortex\Events\Action\PromptVersionsListingActionEvent;
use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Models\PromptVersion;

final class ListPromptVersionsAction
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
     * @return LengthAwarePaginator<int, PromptVersion>
     */
    public function execute(Prompt $prompt, ?int $page = null): LengthAwarePaginator
    {
        PromptVersionsListingActionEvent::dispatch($prompt, $page);

        $result = $this->perform($prompt, $page);

        PromptVersionsListedActionEvent::dispatch($prompt, $result);

        return $result;
    }

    /**
     * @return LengthAwarePaginator<int, PromptVersion>
     */
    private function perform(Prompt $prompt, ?int $page = null): LengthAwarePaginator
    {
        return $prompt->versions()
            ->orderByDesc('version')
            ->paginate(page: $page);
    }
}
