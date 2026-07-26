<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use Illuminate\Validation\ValidationException;
use JayI\Cortex\Models\Prompt;

final class DeletePromptAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [];
    }

    public function execute(Prompt $prompt): void
    {
        if ($prompt->agents()->exists()) {
            throw ValidationException::withMessages([
                'prompt' => 'The prompt is attached to one or more agents and cannot be deleted.',
            ]);
        }

        $prompt->delete();
    }
}
