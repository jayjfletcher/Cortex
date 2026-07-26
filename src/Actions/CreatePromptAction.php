<?php

declare(strict_types=1);

namespace JayI\Cortex\Actions;

use Illuminate\Support\Facades\DB;
use JayI\Cortex\Models\Prompt;

final class CreatePromptAction
{
    /**
     * @return array<string, mixed>
     */
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:cortex_prompts,slug'],
            'description' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'publish' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Prompt
    {
        return DB::transaction(function () use ($data): Prompt {
            $prompt = Prompt::query()->create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'description' => $data['description'] ?? null,
            ]);

            $version = $prompt->versions()->create([
                'version' => 1,
                'content' => $data['content'],
            ]);

            if ($data['publish'] ?? true) {
                $prompt->published_version_id = $version->getKey();
                $prompt->save();
            }

            return $prompt->load('publishedVersion');
        });
    }
}
