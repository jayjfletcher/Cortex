<x-atrium::layout :title="__('cortex::cortex.servers')">
    <x-atrium::page-header :title="__('cortex::cortex.servers')" />

    <div class="mt-5">
        @if ($servers === [])
            <x-atrium::empty-state :title="__('cortex::cortex.no_servers')" />
        @else
            <x-atrium::table striped>
                <x-slot:head>
                    <x-atrium::table.row>
                        <x-atrium::table.cell heading>{{ __('cortex::cortex.name') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('cortex::cortex.instructions') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('cortex::cortex.actions') }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                </x-slot:head>

                @foreach ($servers as $server)
                    <x-atrium::table.row>
                        <x-atrium::table.cell><code class="text-xs">{{ $server['name'] }}</code></x-atrium::table.cell>
                        <x-atrium::table.cell class="max-w-md truncate">{{ $server['instructions'] }}</x-atrium::table.cell>
                        <x-atrium::table.cell>
                            <x-atrium::button size="sm" variant="outline"
                                              :href="route('atrium.cortex.servers.instructions', $server['name'])">
                                {{ __('cortex::cortex.instructions') }}
                            </x-atrium::button>
                        </x-atrium::table.cell>
                    </x-atrium::table.row>
                @endforeach
            </x-atrium::table>
        @endif
    </div>
</x-atrium::layout>
