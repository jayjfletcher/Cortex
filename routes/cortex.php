<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use JayI\Cortex\Http\Controllers\AgentController;
use JayI\Cortex\Http\Controllers\AgentRunController;
use JayI\Cortex\Http\Controllers\McpInstructionController;
use JayI\Cortex\Http\Controllers\McpServerController;
use JayI\Cortex\Http\Controllers\PromptController;
use JayI\Cortex\Http\Controllers\PromptVersionController;
use JayI\Cortex\Http\Controllers\ProviderController;
use JayI\Cortex\Http\Controllers\ToolController;
use JayI\Cortex\Http\Controllers\ToolDescriptionController;

/** @var string $prefix */
$prefix = config('cortex.routes.prefix');

/** @var array<int, string> $middleware */
$middleware = config('cortex.routes.middleware');

Route::prefix($prefix)->middleware($middleware)->name('cortex.')->group(function (): void {
    Route::get('prompts', [PromptController::class, 'index'])->name('prompts.index');
    Route::post('prompts', [PromptController::class, 'store'])->name('prompts.store');
    Route::get('prompts/{prompt:slug}', [PromptController::class, 'show'])->name('prompts.show');
    Route::patch('prompts/{prompt:slug}', [PromptController::class, 'update'])->name('prompts.update');
    Route::delete('prompts/{prompt:slug}', [PromptController::class, 'destroy'])->name('prompts.destroy');

    Route::get('prompts/{prompt:slug}/versions', [PromptVersionController::class, 'index'])->name('prompts.versions.index');
    Route::post('prompts/{prompt:slug}/versions', [PromptVersionController::class, 'store'])->name('prompts.versions.store');
    Route::get('prompts/{prompt:slug}/versions/{version}', [PromptVersionController::class, 'show'])
        ->whereNumber('version')->name('prompts.versions.show');
    Route::post('prompts/{prompt:slug}/versions/{version}/publish', [PromptVersionController::class, 'publish'])
        ->whereNumber('version')->name('prompts.versions.publish');

    Route::get('agents', [AgentController::class, 'index'])->name('agents.index');
    Route::post('agents', [AgentController::class, 'store'])->name('agents.store');
    Route::get('agents/{agent:slug}', [AgentController::class, 'show'])->name('agents.show');
    Route::patch('agents/{agent:slug}', [AgentController::class, 'update'])->name('agents.update');
    Route::delete('agents/{agent:slug}', [AgentController::class, 'destroy'])->name('agents.destroy');

    Route::post('agents/{agent:slug}/run', [AgentRunController::class, 'store'])->name('agents.run');

    Route::get('providers', [ProviderController::class, 'index'])->name('providers.index');

    Route::get('tools', [ToolController::class, 'index'])->name('tools.index');

    Route::get('tools/{tool}/description', [ToolDescriptionController::class, 'show'])->name('tools.description.show');
    Route::delete('tools/{tool}/description', [ToolDescriptionController::class, 'destroy'])->name('tools.description.destroy');
    Route::get('tools/{tool}/description/versions', [ToolDescriptionController::class, 'versions'])->name('tools.description.versions.index');
    Route::post('tools/{tool}/description/versions', [ToolDescriptionController::class, 'store'])->name('tools.description.versions.store');
    Route::post('tools/{tool}/description/versions/{version}/publish', [ToolDescriptionController::class, 'publish'])
        ->whereNumber('version')->name('tools.description.versions.publish');

    Route::get('servers', [McpServerController::class, 'index'])->name('servers.index');

    Route::get('servers/{server}/instructions', [McpInstructionController::class, 'show'])->name('servers.instructions.show');
    Route::delete('servers/{server}/instructions', [McpInstructionController::class, 'destroy'])->name('servers.instructions.destroy');
    Route::get('servers/{server}/instructions/versions', [McpInstructionController::class, 'versions'])->name('servers.instructions.versions.index');
    Route::post('servers/{server}/instructions/versions', [McpInstructionController::class, 'store'])->name('servers.instructions.versions.store');
    Route::post('servers/{server}/instructions/versions/{version}/publish', [McpInstructionController::class, 'publish'])
        ->whereNumber('version')->name('servers.instructions.versions.publish');
});
