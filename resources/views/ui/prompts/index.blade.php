<x-atrium::layout :title="__('cortex::cortex.prompts')">
    <x-atrium::page-header :title="__('cortex::cortex.prompts')">
        <x-slot:actions>
            <x-atrium::button :href="route('atrium.cortex.prompts.create')" data-testid="new-prompt">
                {{ __('cortex::cortex.new_prompt') }}
            </x-atrium::button>
        </x-slot:actions>
    </x-atrium::page-header>

    <div class="mt-5">
        @include('cortex::ui.partials.status')

        @if ($prompts->isEmpty())
            <x-atrium::empty-state :title="__('cortex::cortex.no_prompts')" />
        @else
            <x-atrium::table striped>
                <x-slot:head>
                    <x-atrium::table.row>
                        <x-atrium::table.cell heading>{{ __('cortex::cortex.name') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('cortex::cortex.slug') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('cortex::cortex.published') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('cortex::cortex.updated') }}</x-atrium::table.cell>
                        <x-atrium::table.cell heading>{{ __('cortex::cortex.actions') }}</x-atrium::table.cell>
                    </x-atrium::table.row>
                </x-slot:head>

                @foreach ($prompts as $prompt)
                    <x-atrium::table.row>
                        <x-atrium::table.cell>
                            <a class="font-medium underline-offset-2 hover:underline"
                               href="{{ route('atrium.cortex.prompts.show', $prompt->slug) }}">{{ $prompt->name }}</a>
                        </x-atrium::table.cell>
                        <x-atrium::table.cell><code class="text-xs">{{ $prompt->slug }}</code></x-atrium::table.cell>
                        <x-atrium::table.cell>
                            @if ($prompt->publishedVersion)
                                <x-atrium::badge variant="success">v{{ $prompt->publishedVersion->version }}</x-atrium::badge>
                            @else
                                <span class="opacity-60">&mdash;</span>
                            @endif
                        </x-atrium::table.cell>
                        <x-atrium::table.cell>{{ $prompt->updated_at?->diffForHumans() }}</x-atrium::table.cell>
                        <x-atrium::table.cell>
                            <div class="flex items-center gap-2">
                                <x-atrium::button size="sm" variant="outline" :href="route('atrium.cortex.prompts.edit', $prompt->slug)">
                                    {{ __('cortex::cortex.edit') }}
                                </x-atrium::button>

                                <form method="POST" action="{{ route('atrium.cortex.prompts.destroy', $prompt->slug) }}"
                                      onsubmit="return confirm(@js(__('cortex::cortex.confirm_delete')))">
                                    @csrf
                                    @method('DELETE')
                                    <x-atrium::button size="sm" variant="danger" type="submit"
                                                      data-testid="delete-{{ $prompt->slug }}">
                                        {{ __('cortex::cortex.delete') }}
                                    </x-atrium::button>
                                </form>
                            </div>
                        </x-atrium::table.cell>
                    </x-atrium::table.row>
                @endforeach
            </x-atrium::table>

            <div class="mt-4">
                <x-atrium::pagination :paginator="$prompts" />
            </div>
        @endif
    </div>
</x-atrium::layout>
