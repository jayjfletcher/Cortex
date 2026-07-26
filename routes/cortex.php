<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use JayI\Cortex\Http\Controllers\AgentController;
use JayI\Cortex\Http\Controllers\AgentRunController;
use JayI\Cortex\Http\Controllers\PromptController;
use JayI\Cortex\Http\Controllers\PromptVersionController;
use JayI\Cortex\Http\Controllers\ToolController;

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

    Route::get('tools', [ToolController::class, 'index'])->name('tools.index');
});
