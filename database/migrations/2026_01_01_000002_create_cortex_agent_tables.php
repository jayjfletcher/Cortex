<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cortex_agents', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->foreignUlid('prompt_id')->nullable()->constrained('cortex_prompts')->restrictOnDelete();
            $table->foreignUlid('prompt_version_id')->nullable()->constrained('cortex_prompt_versions')->restrictOnDelete();
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->json('settings')->nullable();
            $table->json('tools')->nullable();
            $table->timestamps();
        });

        Schema::create('cortex_agent_agent', function (Blueprint $table): void {
            $table->foreignUlid('agent_id')->constrained('cortex_agents')->cascadeOnDelete();
            $table->foreignUlid('sub_agent_id')->constrained('cortex_agents')->cascadeOnDelete();

            $table->unique(['agent_id', 'sub_agent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cortex_agent_agent');
        Schema::dropIfExists('cortex_agents');
    }
};
