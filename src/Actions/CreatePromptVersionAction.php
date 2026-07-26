<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use Illuminate\Support\Facades\DB;
use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Models\PromptVersion;

final class CreatePromptVersionAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'content' => ['required', 'string'],
            'publish' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Prompt $prompt, array $data): PromptVersion
    {
        return DB::transaction(function () use ($prompt, $data): PromptVersion {
            /** @var PromptVersion $version */
            $version = $prompt->versions()->create([
                'version' => ((int) $prompt->versions()->lockForUpdate()->max('version')) + 1,
                'content' => $data['content'],
            ]);

            if ($data['publish'] ?? false) {
                $prompt->published_version_id = $version->getKey();
                $prompt->save();
            }

            return $version;
        });
    }
}
