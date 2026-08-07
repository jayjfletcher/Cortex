<script setup>
import { computed, onMounted, ref } from 'vue';
import servers from '../api/servers';
import { ApiError } from '../api/client';
import Alert from '../components/Alert.vue';
import ConfirmButton from '../components/ConfirmButton.vue';
import FieldErrors from '../components/FieldErrors.vue';
import Spinner from '../components/Spinner.vue';

const props = defineProps({
    server: { type: String, required: true },
});

const codeInstructions = ref(null);
const instructions = ref(null);
const versions = ref([]);
const expanded = ref(null);
const loading = ref(true);
const error = ref(null);
const success = ref(null);

const newVersion = ref({ content: '', publish: true });
const savingVersion = ref(false);
const errors = ref({});

const hasOverride = computed(() => instructions.value !== null);
const effectiveInstructions = computed(
    () => instructions.value?.published_content ?? codeInstructions.value,
);

async function load() {
    loading.value = true;
    error.value = null;

    try {
        const listed = (await servers.list()).data.find((item) => item.name === props.server);
        codeInstructions.value = listed?.instructions ?? null;

        const [showResponse, versionsResponse] = await Promise.all([
            servers.instructions(props.server),
            servers.instructionVersions(props.server),
        ]);

        instructions.value = showResponse.data;
        versions.value = versionsResponse.data;
    } catch (e) {
        if (e instanceof ApiError && e.status === 404) {
            instructions.value = null;
            versions.value = [];
        } else {
            error.value = e.message;
        }
    } finally {
        loading.value = false;
    }
}

async function publish(version) {
    error.value = null;
    success.value = null;

    try {
        await servers.publishInstructionVersion(props.server, version);
        success.value = `Version ${version} published.`;
        await load();
    } catch (e) {
        error.value = e.message;
    }
}

async function createVersion() {
    savingVersion.value = true;
    error.value = null;
    success.value = null;
    errors.value = {};

    try {
        await servers.createInstructionVersion(props.server, {
            content: newVersion.value.content,
            publish: newVersion.value.publish,
        });
        newVersion.value = { content: '', publish: true };
        success.value = 'Version created.';
        await load();
    } catch (e) {
        if (e instanceof ApiError && e.status === 422) {
            errors.value = e.errors;
        } else {
            error.value = e.message;
        }
    } finally {
        savingVersion.value = false;
    }
}

async function removeOverride() {
    error.value = null;
    success.value = null;

    try {
        await servers.destroyInstructions(props.server);
        success.value = 'Override removed. The code-declared instructions are live again.';
        await load();
    } catch (e) {
        error.value = e.message;
    }
}

onMounted(load);
</script>

<template>
    <div>
        <div class="page-header">
            <h1><code>{{ server }}</code> instructions</h1>
            <router-link class="button" :to="{ name: 'servers.index' }">Back to servers</router-link>
        </div>
        <Alert :message="error" />
        <Alert type="success" :message="success" />
        <Spinner v-if="loading" />
        <template v-else>
            <h2>Live instructions</h2>
            <p>
                {{ effectiveInstructions }}
                <span v-if="instructions?.published_version" class="badge">override v{{ instructions.published_version }}</span>
                <span v-else class="badge">from code</span>
            </p>
            <p v-if="hasOverride && instructions.published_version" class="muted">
                Code-declared instructions stay dormant until the override is removed.
            </p>
            <p v-else-if="hasOverride" class="muted">
                Draft versions exist but none is published — the code-declared instructions are live.
            </p>

            <h2>Versions</h2>
            <p v-if="!versions.length" class="muted">No versions yet. Create one below to override the instructions.</p>
            <table v-else>
                <thead>
                    <tr>
                        <th>Version</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="version in versions" :key="version.version">
                        <tr>
                            <td>
                                v{{ version.version }}
                                <span v-if="instructions?.published_version === version.version" class="badge">published</span>
                            </td>
                            <td class="muted">{{ new Date(version.created_at).toLocaleString() }}</td>
                            <td>
                                <div class="actions">
                                    <button type="button" @click="expanded = expanded === version.version ? null : version.version">
                                        {{ expanded === version.version ? 'Hide' : 'View' }}
                                    </button>
                                    <button
                                        v-if="instructions?.published_version !== version.version"
                                        type="button"
                                        @click="publish(version.version)"
                                    >
                                        Publish
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="expanded === version.version">
                            <td colspan="3"><pre>{{ version.content }}</pre></td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <h2>New version</h2>
            <form @submit.prevent="createVersion">
                <div class="field">
                    <label for="instructions-content">Content</label>
                    <textarea id="instructions-content" v-model="newVersion.content" class="code" required></textarea>
                    <FieldErrors :errors="errors" field="content" />
                </div>
                <div class="field">
                    <label>
                        <input v-model="newVersion.publish" type="checkbox" style="width: auto" />
                        Publish immediately
                    </label>
                </div>
                <button class="primary" type="submit" :disabled="savingVersion">
                    {{ savingVersion ? 'Saving…' : 'Create version' }}
                </button>
            </form>

            <template v-if="hasOverride">
                <h2>Remove override</h2>
                <p class="muted">Deletes the override and its whole version history; the code-declared instructions take over.</p>
                <ConfirmButton label="Remove override" @confirm="removeOverride" />
            </template>
        </template>
    </div>
</template>
