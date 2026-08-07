<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cortex_mcp_instructions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('server')->unique();
            // No FK: circular reference with cortex_mcp_instruction_versions;
            // integrity is enforced when publishing.
            $table->ulid('published_version_id')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('cortex_mcp_instruction_versions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('mcp_instruction_id')->constrained('cortex_mcp_instructions')->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->text('content');
            $table->timestamps();

            $table->unique(['mcp_instruction_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cortex_mcp_instruction_versions');
        Schema::dropIfExists('cortex_mcp_instructions');
    }
};
