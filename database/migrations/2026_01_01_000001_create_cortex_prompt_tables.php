<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cortex_prompts', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            // No FK: circular reference with cortex_prompt_versions; integrity
            // is enforced when publishing.
            $table->ulid('published_version_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('cortex_prompt_versions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('prompt_id')->constrained('cortex_prompts')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->longText('content');
            $table->timestamps();

            $table->unique(['prompt_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cortex_prompt_versions');
        Schema::dropIfExists('cortex_prompts');
    }
};
