<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use JayI\Cortex\Models\Prompt;

final class ListPromptsAction
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
     * @return LengthAwarePaginator<int, Prompt>
     */
    public function execute(?int $page = null): LengthAwarePaginator
    {
        return Prompt::query()
            ->with('publishedVersion')
            ->orderBy('name')
            ->paginate(page: $page);
    }
}
