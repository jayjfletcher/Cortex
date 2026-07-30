<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import agents from '../../api/agents';
import prompts from '../../api/prompts';
import providers from '../../api/providers';
import tools from '../../api/tools';
import fetchAllPages from '../../api/paginate';
import { ApiError } from '../../api/client';
import Alert from '../../components/Alert.vue';
import FieldErrors from '../../components/FieldErrors.vue';
import Spinner from '../../components/Spinner.vue';

const props = defineProps({
    slug: { type: String, default: null },
});

const router = useRouter();
const editing = Boolean(props.slug);

const form = ref({
    name: '',
    slug: '',
    description: '',
    provider: '',
    model: '',
    settings: { temperature: '', max_steps: '', max_tokens: '', top_p: '' },
    tools: [],
    prompt: '',
    prompt_version: '',
    sub_agents: [],
});

const toolOptions = ref([]);
const promptOptions = ref([]);
const agentOptions = ref([]);
const providerOptions = ref([]);

// Options include the saved value even when the provider list no longer
// offers it, so editing an agent never silently drops its configuration.
const providerNames = computed(() => {
    const names = providerOptions.value.map((provider) => provider.name);

    if (form.value.provider && !names.includes(form.value.provider)) {
        names.unshift(form.value.provider);
    }

    return names;
});

const modelOptions = computed(() => {
    const selected = providerOptions.value.find((provider) => provider.name === form.value.provider);

    const models = selected
        ? [...selected.models]
        : [...new Set(providerOptions.value.flatMap((provider) => provider.models))];

    if (form.value.model && !models.includes(form.value.model)) {
        models.unshift(form.value.model);
    }

    return models;
});

const errors = ref({});
const error = ref(null);
const loading = ref(true);
const saving = ref(false);

onMounted(async () => {
    try {
        const [toolsResponse, promptItems, agentItems, providersResponse] = await Promise.all([
            tools.list(),
            fetchAllPages(prompts.list),
            fetchAllPages(agents.list),
            providers.list(),
        ]);

        toolOptions.value = toolsResponse.data;
        providerOptions.value = providersResponse.data;
        promptOptions.value = promptItems;
        agentOptions.value = agentItems.filter((agent) => agent.slug !== props.slug);

        if (editing) {
            const { data } = await agents.show(props.slug);

            form.value.name = data.name;
            form.value.slug = data.slug;
            form.value.description = data.description ?? '';
            form.value.provider = data.provider ?? '';
            form.value.model = data.model ?? '';
            form.value.tools = data.tools ?? [];
            form.value.prompt = data.prompt ?? '';
            form.value.prompt_version = data.prompt_version ?? '';
            form.value.sub_agents = data.sub_agents ?? [];

            for (const key of Object.keys(form.value.settings)) {
                form.value.settings[key] = data.settings?.[key] ?? '';
            }
        }
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
});

// Switching provider swaps the model to that provider's default unless the
// current model is valid there. Skipped while the form hydrates so a saved
// custom model is never clobbered on load.
watch(() => form.value.provider, (name) => {
    if (loading.value) {
        return;
    }

    const selected = providerOptions.value.find((provider) => provider.name === name);

    if (selected && !selected.models.includes(form.value.model)) {
        form.value.model = selected.default_model ?? '';
    }
});

function payload() {
    const settings = {};

    for (const [key, value] of Object.entries(form.value.settings)) {
        if (value !== '' && value !== null) {
            settings[key] = Number(value);
        }
    }

    return {
        name: form.value.name,
        description: form.value.description || null,
        provider: form.value.provider || null,
        model: form.value.model || null,
        settings: Object.keys(settings).length ? settings : null,
        tools: form.value.tools,
        prompt: form.value.prompt || null,
        prompt_version: form.value.prompt_version ? Number(form.value.prompt_version) : null,
        sub_agents: form.value.sub_agents,
    };
}

async function save() {
    saving.value = true;
    error.value = null;
    errors.value = {};

    try {
        if (editing) {
            await agents.update(props.slug, payload());
        } else {
            await agents.create({ ...payload(), slug: form.value.slug });
        }

        router.push({ name: 'agents.index' });
    } catch (e) {
        if (e instanceof ApiError && e.status === 422) {
            errors.value = e.errors;
        } else {
            error.value = e.message;
        }
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div>
        <div class="page-header">
            <h1>{{ editing ? 'Edit agent' : 'New agent' }}</h1>
        </div>
        <Alert :message="error" />
        <Spinner v-if="loading" />
        <form v-else @submit.prevent="save">
            <div class="field">
                <label for="name">Name</label>
                <input id="name" v-model="form.name" type="text" required />
                <FieldErrors :errors="errors" field="name" />
            </div>
            <div v-if="!editing" class="field">
                <label for="slug">Slug</label>
                <input id="slug" v-model="form.slug" type="text" required />
                <p class="hint">Letters, numbers, dashes, underscores. Cannot be changed later.</p>
                <FieldErrors :errors="errors" field="slug" />
            </div>
            <div class="field">
                <label for="description">Description</label>
                <textarea id="description" v-model="form.description" rows="2"></textarea>
                <FieldErrors :errors="errors" field="description" />
            </div>
            <div class="field">
                <label for="provider">Provider</label>
                <select id="provider" v-model="form.provider">
                    <option value="">Provider default</option>
                    <option v-for="name in providerNames" :key="name" :value="name">{{ name }}</option>
                </select>
                <FieldErrors :errors="errors" field="provider" />
            </div>
            <div class="field">
                <label for="model">Model</label>
                <select id="model" v-model="form.model">
                    <option value="">Provider default</option>
                    <option v-for="model in modelOptions" :key="model" :value="model">{{ model }}</option>
                </select>
                <FieldErrors :errors="errors" field="model" />
            </div>

            <div class="field">
                <label>Settings</label>
                <div class="settings-grid">
                    <div>
                        <label class="hint" for="temperature">Temperature (0–2)</label>
                        <input id="temperature" v-model="form.settings.temperature" type="number" step="0.1" min="0" max="2" />
                    </div>
                    <div>
                        <label class="hint" for="max_steps">Max steps</label>
                        <input id="max_steps" v-model="form.settings.max_steps" type="number" min="1" />
                    </div>
                    <div>
                        <label class="hint" for="max_tokens">Max tokens</label>
                        <input id="max_tokens" v-model="form.settings.max_tokens" type="number" min="1" />
                    </div>
                    <div>
                        <label class="hint" for="top_p">Top P (0–1)</label>
                        <input id="top_p" v-model="form.settings.top_p" type="number" step="0.05" min="0" max="1" />
                    </div>
                </div>
                <FieldErrors :errors="errors" field="settings" />
            </div>

            <div class="field">
                <label>Tools</label>
                <p v-if="!toolOptions.length" class="hint">No tools registered.</p>
                <div v-else class="checkbox-list">
                    <label v-for="tool in toolOptions" :key="tool.name">
                        <input v-model="form.tools" type="checkbox" :value="tool.name" />
                        {{ tool.name }}
                    </label>
                </div>
                <FieldErrors :errors="errors" field="tools" />
            </div>

            <div class="field">
                <label for="prompt">Prompt</label>
                <select id="prompt" v-model="form.prompt">
                    <option value="">None</option>
                    <option v-for="prompt in promptOptions" :key="prompt.slug" :value="prompt.slug">
                        {{ prompt.name }} ({{ prompt.slug }})
                    </option>
                </select>
                <FieldErrors :errors="errors" field="prompt" />
            </div>
            <div v-if="form.prompt" class="field">
                <label for="prompt_version">Pinned prompt version</label>
                <input id="prompt_version" v-model="form.prompt_version" type="number" min="1" placeholder="Latest published" />
                <p class="hint">Leave blank to always use the published version.</p>
                <FieldErrors :errors="errors" field="prompt_version" />
            </div>

            <div class="field">
                <label>Sub-agents</label>
                <p v-if="!agentOptions.length" class="hint">No other agents available.</p>
                <div v-else class="checkbox-list">
                    <label v-for="agent in agentOptions" :key="agent.slug">
                        <input v-model="form.sub_agents" type="checkbox" :value="agent.slug" />
                        {{ agent.name }}
                    </label>
                </div>
                <FieldErrors :errors="errors" field="sub_agents" />
            </div>

            <button class="primary" type="submit" :disabled="saving">
                {{ saving ? 'Saving…' : 'Save' }}
            </button>
        </form>
    </div>
</template>
