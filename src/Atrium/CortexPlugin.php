<?php

declare(strict_types=1);

namespace JayI\Cortex\Atrium;

use Atrium\Atrium\Navigation\NavItem;
use Atrium\Atrium\Plugins\Plugin;
use Atrium\Atrium\Search\SearchResult;
use Atrium\Atrium\Search\SearchSource;
use Atrium\Atrium\Settings\SettingsPanel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;
use JayI\Cortex\Http\Ui\AgentUiController;
use JayI\Cortex\Http\Ui\McpInstructionUiController;
use JayI\Cortex\Http\Ui\PromptUiController;
use JayI\Cortex\Http\Ui\PromptVersionUiController;
use JayI\Cortex\Http\Ui\RunAgentUiController;
use JayI\Cortex\Http\Ui\ServerUiController;
use JayI\Cortex\Http\Ui\ToolDescriptionUiController;
use JayI\Cortex\Http\Ui\ToolUiController;
use JayI\Cortex\Mcp\McpServerRegistry;
use JayI\Cortex\Models\Agent;
use JayI\Cortex\Models\Prompt;
use JayI\Cortex\Tools\ToolRegistry;

/**
 * Registers Cortex inside the Atrium dashboard.
 *
 * Routes are declared here rather than in a route file so they land inside
 * Atrium's group, inheriting its prefix, middleware and authorization gate.
 */
class CortexPlugin extends Plugin
{
    public function key(): string
    {
        return 'cortex';
    }

    public function label(): string
    {
        return 'Cortex';
    }

    public function navigation(): array
    {
        return [
            NavItem::make(__('cortex::cortex.prompts'))->route('atrium.cortex.prompts.index')->group('Cortex')->sort(10),
            NavItem::make(__('cortex::cortex.agents'))->route('atrium.cortex.agents.index')->group('Cortex')->sort(20),
            NavItem::make(__('cortex::cortex.run_agent'))->route('atrium.cortex.run')->group('Cortex')->sort(30),
            NavItem::make(__('cortex::cortex.tools'))->route('atrium.cortex.tools.index')->group('Cortex')->sort(40),
            NavItem::make(__('cortex::cortex.servers'))->route('atrium.cortex.servers.index')->group('Cortex')->sort(50),
        ];
    }

    public function routes(): void
    {
        Route::name('cortex.')->group(function (): void {
            Route::get('cortex/prompts', [PromptUiController::class, 'index'])->name('prompts.index');
            Route::get('cortex/prompts/create', [PromptUiController::class, 'create'])->name('prompts.create');
            Route::post('cortex/prompts', [PromptUiController::class, 'store'])->name('prompts.store');
            Route::get('cortex/prompts/{prompt:slug}', [PromptUiController::class, 'show'])->name('prompts.show');
            Route::get('cortex/prompts/{prompt:slug}/edit', [PromptUiController::class, 'edit'])->name('prompts.edit');
            Route::put('cortex/prompts/{prompt:slug}', [PromptUiController::class, 'update'])->name('prompts.update');
            Route::delete('cortex/prompts/{prompt:slug}', [PromptUiController::class, 'destroy'])->name('prompts.destroy');

            Route::post('cortex/prompts/{prompt:slug}/versions', [PromptVersionUiController::class, 'store'])->name('prompts.versions.store');
            Route::post('cortex/prompts/{prompt:slug}/versions/{version}/publish', [PromptVersionUiController::class, 'publish'])
                ->whereNumber('version')->name('prompts.versions.publish');

            Route::get('cortex/agents', [AgentUiController::class, 'index'])->name('agents.index');
            Route::get('cortex/agents/create', [AgentUiController::class, 'create'])->name('agents.create');
            Route::post('cortex/agents', [AgentUiController::class, 'store'])->name('agents.store');
            Route::get('cortex/agents/{agent:slug}/edit', [AgentUiController::class, 'edit'])->name('agents.edit');
            Route::put('cortex/agents/{agent:slug}', [AgentUiController::class, 'update'])->name('agents.update');
            Route::delete('cortex/agents/{agent:slug}', [AgentUiController::class, 'destroy'])->name('agents.destroy');

            Route::get('cortex/run', [RunAgentUiController::class, 'create'])->name('run');
            Route::post('cortex/run', [RunAgentUiController::class, 'store'])->name('run.store');

            Route::get('cortex/tools', [ToolUiController::class, 'index'])->name('tools.index');
            Route::get('cortex/tools/{tool}/description', [ToolDescriptionUiController::class, 'show'])->name('tools.description');
            Route::post('cortex/tools/{tool}/description/versions', [ToolDescriptionUiController::class, 'store'])->name('tools.description.store');
            Route::post('cortex/tools/{tool}/description/versions/{version}/publish', [ToolDescriptionUiController::class, 'publish'])
                ->whereNumber('version')->name('tools.description.publish');
            Route::delete('cortex/tools/{tool}/description', [ToolDescriptionUiController::class, 'destroy'])->name('tools.description.destroy');

            Route::get('cortex/servers', [ServerUiController::class, 'index'])->name('servers.index');
            Route::get('cortex/servers/{server}/instructions', [McpInstructionUiController::class, 'show'])->name('servers.instructions');
            Route::post('cortex/servers/{server}/instructions/versions', [McpInstructionUiController::class, 'store'])->name('servers.instructions.store');
            Route::post('cortex/servers/{server}/instructions/versions/{version}/publish', [McpInstructionUiController::class, 'publish'])
                ->whereNumber('version')->name('servers.instructions.publish');
            Route::delete('cortex/servers/{server}/instructions', [McpInstructionUiController::class, 'destroy'])->name('servers.instructions.destroy');
        });
    }

    public function settings(): ?SettingsPanel
    {
        return SettingsPanel::make('cortex')
            ->label(__('cortex::cortex.settings_label'))
            ->description(__('cortex::cortex.settings_description'))
            ->view('cortex::ui.settings')
            ->resolve(fn (): array => [
                'providers' => (array) config('cortex.providers', []),
                'toolCount' => count(app(ToolRegistry::class)->all()),
                'serverCount' => count(app(McpServerRegistry::class)->all()),
                'cacheEnabled' => (bool) config('cortex.cache.enabled', true),
            ]);
    }

    public function search(): ?SearchSource
    {
        return SearchSource::make('cortex')
            ->label(__('cortex::cortex.label'))
            ->using(function (string $query): array {
                $prompts = Prompt::query()
                    ->where(fn (Builder $builder): Builder => $builder->where('name', 'like', '%'.$query.'%')->orWhere('slug', 'like', '%'.$query.'%'))
                    ->limit(5)
                    ->get()
                    ->map(fn (Prompt $prompt): SearchResult => SearchResult::make(
                        $prompt->name,
                        route('atrium.cortex.prompts.show', $prompt->slug),
                    )->subtitle($prompt->slug)->group(__('cortex::cortex.prompts')))
                    ->all();

                $agents = Agent::query()
                    ->where(fn (Builder $builder): Builder => $builder->where('name', 'like', '%'.$query.'%')->orWhere('slug', 'like', '%'.$query.'%'))
                    ->limit(5)
                    ->get()
                    ->map(fn (Agent $agent): SearchResult => SearchResult::make(
                        $agent->name,
                        route('atrium.cortex.agents.edit', $agent->slug),
                    )->subtitle($agent->slug)->group(__('cortex::cortex.agents')))
                    ->all();

                return [...$prompts, ...$agents];
            });
    }
}
