@php($editing = $prompt !== null)

<x-atrium::layout :title="$editing ? __('cortex::cortex.edit_prompt') : __('cortex::cortex.new_prompt')">
    <x-atrium::page-header :title="$editing ? __('cortex::cortex.edit_prompt') : __('cortex::cortex.new_prompt')" />

    <div class="mt-5 max-w-2xl">
        <x-atrium::card>
            <form method="POST"
                  action="{{ $editing ? route('atrium.cortex.prompts.update', $prompt->slug) : route('atrium.cortex.prompts.store') }}"
                  class="flex flex-col gap-4">
                @csrf
                @if ($editing)
                    @method('PUT')
                @endif

                <x-atrium::form.input name="name" :label="__('cortex::cortex.name')" :value="$prompt?->name" required />

                @unless ($editing)
                    <x-atrium::form.input name="slug" :label="__('cortex::cortex.slug')" :hint="__('cortex::cortex.slug_hint')" required />
                @endunless

                <x-atrium::form.textarea name="description" :label="__('cortex::cortex.description')" :value="$prompt?->description" />

                @if ($editing)
                    <p class="text-sm text-on-surface dark:text-on-surface-dark">{{ __('cortex::cortex.versioned_hint') }}</p>
                @else
                    <x-atrium::form.textarea name="content" :label="__('cortex::cortex.content')" rows="10" required class="font-mono text-xs" />
                    <x-atrium::form.checkbox name="publish" :label="__('cortex::cortex.publish_immediately')" :checked="true" />
                @endif

                <div class="flex items-center gap-2">
                    <x-atrium::button type="submit" data-testid="save-prompt">{{ __('cortex::cortex.save') }}</x-atrium::button>
                    <x-atrium::button variant="ghost" :href="route('atrium.cortex.prompts.index')">{{ __('cortex::cortex.cancel') }}</x-atrium::button>
                </div>
            </form>
        </x-atrium::card>
    </div>
</x-atrium::layout>
