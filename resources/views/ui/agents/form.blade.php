@php($editing = $agent !== null)
@php($settings = (array) ($agent?->settings ?? []))

{{-- Provider and model options are assembled server-side so a value saved
     earlier stays selectable even when the provider no longer lists it.
     Alpine only handles swapping the model when the provider changes. --}}
<x-atrium::layout :title="$editing ? __('cortex::cortex.edit_agent') : __('cortex::cortex.new_agent')">
    <x-atrium::page-header :title="$editing ? __('cortex::cortex.edit_agent') : __('cortex::cortex.new_agent')" />

    <div class="mt-5 max-w-2xl">
        <x-atrium::card>
            <form method="POST"
                  action="{{ $editing ? route('atrium.cortex.agents.update', $agent->slug) : route('atrium.cortex.agents.store') }}"
                  class="flex flex-col gap-4"
                  x-data="cortexAgentForm({
                      defaults: @js(collect($providers)->mapWithKeys(fn ($provider) => [$provider['name'] => $provider['default_model'] ?? null])),
                      model: @js(old('model', $agent?->model)),
                  })">
                @csrf
                @if ($editing)
                    @method('PUT')
                @endif

                <x-atrium::form.input name="name" :label="__('cortex::cortex.name')" :value="$agent?->name" required />

                @unless ($editing)
                    <x-atrium::form.input name="slug" :label="__('cortex::cortex.slug')" :hint="__('cortex::cortex.slug_hint')" required />
                @endunless

                <x-atrium::form.textarea name="description" :label="__('cortex::cortex.description')" :value="$agent?->description" />

                <x-atrium::form.select
                    name="provider"
                    :label="__('cortex::cortex.provider')"
                    :placeholder="__('cortex::cortex.provider_default')"
                    :options="collect($providerNames)->mapWithKeys(fn ($name) => [$name => $name])"
                    :selected="$agent?->provider"
                    x-model="provider"
                    x-on:change="syncModel()" />

                <x-atrium::form.select
                    name="model"
                    :label="__('cortex::cortex.model')"
                    :placeholder="__('cortex::cortex.provider_default')"
                    :options="collect($providers)->flatMap(fn ($provider) => $provider['models'] ?? [])->push($agent?->model)->filter()->unique()->mapWithKeys(fn ($model) => [$model => $model])"
                    :selected="$agent?->model"
                    x-model="model" />

                <x-atrium::section :title="__('cortex::cortex.settings')">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <x-atrium::form.input type="number" step="0.1" min="0" max="2"
                                              name="settings[temperature]" :label="__('cortex::cortex.temperature')"
                                              :value="$settings['temperature'] ?? null" />
                        <x-atrium::form.input type="number" min="1"
                                              name="settings[max_steps]" :label="__('cortex::cortex.max_steps')"
                                              :value="$settings['max_steps'] ?? null" />
                        <x-atrium::form.input type="number" min="1"
                                              name="settings[max_tokens]" :label="__('cortex::cortex.max_tokens')"
                                              :value="$settings['max_tokens'] ?? null" />
                        <x-atrium::form.input type="number" step="0.05" min="0" max="1"
                                              name="settings[top_p]" :label="__('cortex::cortex.top_p')"
                                              :value="$settings['top_p'] ?? null" />
                    </div>
                </x-atrium::section>

                @if ($tools !== [])
                    <x-atrium::section :title="__('cortex::cortex.tools')">
                        <div class="flex flex-col gap-2">
                            @foreach ($tools as $tool)
                                <x-atrium::form.checkbox
                                    name="tools[]"
                                    :value="$tool['name']"
                                    :label="$tool['name']"
                                    :id="'tool-'.$tool['name']"
                                    :checked="in_array($tool['name'], (array) ($agent?->tools ?? []), true)" />
                            @endforeach
                        </div>
                    </x-atrium::section>
                @endif

                <x-atrium::form.select
                    name="prompt"
                    :label="__('cortex::cortex.prompt')"
                    :placeholder="__('cortex::cortex.no_prompt')"
                    :options="$prompts->mapWithKeys(fn ($prompt) => [$prompt->slug => $prompt->name.' ('.$prompt->slug.')'])"
                    :selected="$agent?->prompt?->slug" />

                <x-atrium::form.input type="number" min="1" name="prompt_version"
                                      :label="__('cortex::cortex.pinned_version')"
                                      :hint="__('cortex::cortex.pinned_version_hint')"
                                      :value="$agent?->pinnedVersion?->version" />

                @if ($agents->isNotEmpty())
                    <x-atrium::section :title="__('cortex::cortex.sub_agents')">
                        <div class="flex flex-col gap-2">
                            @foreach ($agents as $option)
                                <x-atrium::form.checkbox
                                    name="sub_agents[]"
                                    :value="$option->slug"
                                    :label="$option->name"
                                    :id="'sub-'.$option->slug"
                                    :checked="in_array($option->slug, $agent?->subAgents?->pluck('slug')->all() ?? [], true)" />
                            @endforeach
                        </div>
                    </x-atrium::section>
                @endif

                <div class="flex items-center gap-2">
                    <x-atrium::button type="submit" data-testid="save-agent">{{ __('cortex::cortex.save') }}</x-atrium::button>
                    <x-atrium::button variant="ghost" :href="route('atrium.cortex.agents.index')">{{ __('cortex::cortex.cancel') }}</x-atrium::button>
                </div>
            </form>
        </x-atrium::card>
    </div>

    @push('atrium-scripts')
        <script>
            window.cortexAgentForm = function (config) {
                return {
                    provider: @js(old('provider', $agent?->provider) ?? ''),
                    model: config.model ?? '',
                    // Choosing a provider moves to its default model, but a
                    // model the user already picked is left alone.
                    syncModel() {
                        const preferred = config.defaults[this.provider]
                        if (preferred && !this.model) this.model = preferred
                    },
                }
            }
        </script>
    @endpush
</x-atrium::layout>
