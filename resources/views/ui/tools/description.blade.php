<x-atrium::layout :title="__('cortex::cortex.tool_description')">
    <x-atrium::page-header :title="$tool" :description="__('cortex::cortex.tool_description')">
        <x-slot:actions>
            <x-atrium::button variant="ghost" :href="route('atrium.cortex.tools.index')">
                {{ __('cortex::cortex.back_to_tools') }}
            </x-atrium::button>
        </x-slot:actions>
    </x-atrium::page-header>

    @include('cortex::ui.partials.override', [
        'override' => $description,
        'fallback' => $codeDescription,
        'versions' => $versions,
        'storeRoute' => route('atrium.cortex.tools.description.store', $tool),
        'destroyRoute' => route('atrium.cortex.tools.description.destroy', $tool),
        'publishRoute' => fn (int $version) => route('atrium.cortex.tools.description.publish', [$tool, $version]),
    ])
</x-atrium::layout>
