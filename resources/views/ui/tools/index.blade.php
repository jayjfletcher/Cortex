<x-atrium::layout :title="__('cortex::cortex.tools')">
    <x-atrium::page-header :title="__('cortex::cortex.tools')" />

    <div class="mt-5">
        @if ($tools === [])
            <x-atrium::empty-state :title="__('cortex::cortex.no_tools')" />
        @else
            <x-atrium::table striped>
                <x-slot:head>
                    <x-atrium::table.row>
                        <x-atrium::table.cell heading>{{ __('cortex::cortex.name') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('cortex::cortex.actions') }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                </x-slot:head>

                @foreach ($tools as $tool)
                    <x-atrium::table.row>
                        <x-atrium::table.cell><code class="text-xs">{{ $tool['name'] }}</code></x-atrium::table.cell>
                        <x-atrium::table.cell>
                            <x-atrium::button size="sm" variant="outline"
                                              :href="route('atrium.cortex.tools.description', $tool['name'])">
                                {{ __('cortex::cortex.description') }}
                            </x-atrium::button>
                        </x-atrium::table.cell>
                    </x-atrium::table.row>
                @endforeach
            </x-atrium::table>
        @endif
    </div>
</x-atrium::layout>
