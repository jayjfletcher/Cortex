<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
        return $prompt->versions()
            ->orderByDesc('version')
            ->paginate(page: $page);
    }
}
