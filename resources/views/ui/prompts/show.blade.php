<x-atrium::layout :title="$prompt->name">
    <x-atrium::page-header :title="$prompt->name" :description="$prompt->description">
        <x-slot:actions>
            <x-atrium::button variant="outline" :href="route('atrium.cortex.prompts.edit', $prompt->slug)">
                {{ __('cortex::cortex.edit') }}
            </x-atrium::button>
        </x-slot:actions>
    </x-atrium::page-header>

    <p class="mt-1 text-sm"><code class="text-xs">{{ $prompt->slug }}</code></p>

    <div class="mt-5 flex flex-col gap-5" x-data="{ expanded: null }">
        @include('cortex::ui.partials.status')

        <x-atrium::card :title="__('cortex::cortex.versions')">
            @if ($versions->isEmpty())
                <x-atrium::empty-state :title="__('cortex::cortex.no_versions')" />
            @else
                <x-atrium::table>
                    <x-slot:head>
                        <x-atrium::table.row>
                            <x-atrium::table.cell heading>{{ __('cortex::cortex.version') }}</x-atrium::table.cell>
                            <x-atrium::table.cell heading>{{ __('cortex::cortex.created') }}</x-atrium::table.cell>
                            <x-atrium::table.cell heading>{{ __('cortex::cortex.actions') }}</x-atrium::table.cell>
                        </x-atrium::table.row>
                    </x-slot:head>

                    @foreach ($versions as $version)
                        @php($isPublished = $prompt->publishedVersion?->version === $version->version)

                        <x-atrium::table.row>
                            <x-atrium::table.cell>
                                v{{ $version->version }}
                                @if ($isPublished)
                                    <x-atrium::badge variant="success">{{ __('cortex::cortex.published') }}</x-atrium::badge>
                                @endif
                            </x-atrium::table.cell>
                            <x-atrium::table.cell>{{ $version->created_at?->diffForHumans() }}</x-atrium::table.cell>
                            <x-atrium::table.cell>
                                <div class="flex items-center gap-2">
                                    {{-- Two buttons rather than one with x-text, so each
                                         label is real markup that exists before Alpine boots. --}}
                                    <x-atrium::button size="sm" variant="ghost"
                                                      x-show="expanded !== {{ $version->version }}"
                                                      x-on:click="expanded = {{ $version->version }}">
                                        {{ __('cortex::cortex.view') }}
                                    </x-atrium::button>

                                    <x-atrium::button size="sm" variant="ghost"
                                                      x-show="expanded === {{ $version->version }}" x-cloak
                                                      x-on:click="expanded = null">
                                        {{ __('cortex::cortex.hide') }}
                                    </x-atrium::button>

                                    @unless ($isPublished)
                                        <form method="POST" action="{{ route('atrium.cortex.prompts.versions.publish', [$prompt->slug, $version->version]) }}">
                                            @csrf
                                            <x-atrium::button size="sm" variant="outline" type="submit"
                                                              data-testid="publish-{{ $version->version }}">
                                                {{ __('cortex::cortex.publish') }}
                                            </x-atrium::button>
                                        </form>
                                    @endunless
                                </div>
                            </x-atrium::table.cell>
                        </x-atrium::table.row>

                        <x-atrium::table.row x-show="expanded === {{ $version->version }}" x-cloak>
                            <x-atrium::table.cell colspan="3">
                                <pre class="overflow-x-auto rounded-radius bg-surface-alt p-3 text-xs dark:bg-surface-dark-alt">{{ $version->content }}</pre>
                            </x-atrium::table.cell>
                        </x-atrium::table.row>
                    @endforeach
                </x-atrium::table>

                <div class="mt-4">
                    <x-atrium::pagination :paginator="$versions" />
                </div>
            @endif
        </x-atrium::card>

        <x-atrium::card :title="__('cortex::cortex.new_version')">
            <form method="POST" action="{{ route('atrium.cortex.prompts.versions.store', $prompt->slug) }}" class="flex flex-col gap-4">
                @csrf
                <x-atrium::form.textarea name="content" :label="__('cortex::cortex.content')" rows="10" required class="font-mono text-xs" />
                <x-atrium::form.checkbox name="publish" :label="__('cortex::cortex.publish_immediately')" />

                <div>
                    <x-atrium::button type="submit" data-testid="add-version">{{ __('cortex::cortex.add_version') }}</x-atrium::button>
                </div>
            </form>
        </x-atrium::card>
    </div>
</x-atrium::layout>
