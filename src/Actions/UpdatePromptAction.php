<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use JayI\Cortex\Models\Prompt;

final class UpdatePromptAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
        ];
    }

    /**
     * @param  array{name?: string, description?: string|null}  $data
     */
    public function execute(Prompt $prompt, array $data): Prompt
    {
        $prompt->fill($data)->save();

        return $prompt->load('publishedVersion');
    }
}
