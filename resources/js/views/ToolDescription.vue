<script setup>
import { computed, onMounted, ref } from 'vue';
import tools from '../api/tools';
import { ApiError } from '../api/client';
import Alert from '../components/Alert.vue';
import ConfirmButton from '../components/ConfirmButton.vue';
import FieldErrors from '../components/FieldErrors.vue';
import Spinner from '../components/Spinner.vue';

const props = defineProps({
    tool: { type: String, required: true },
});

const codeDescription = ref(null);
const description = ref(null);
const versions = ref([]);
const expanded = ref(null);
const loading = ref(true);
const error = ref(null);
const success = ref(null);

const newVersion = ref({ content: '', publish: true });
const savingVersion = ref(false);
const errors = ref({});

const hasOverride = computed(() => description.value !== null);
const effectiveDescription = computed(
    () => description.value?.published_content ?? codeDescription.value,
);

async function load() {
    loading.value = true;
    error.value = null;

    try {
        const listed = (await tools.list()).data.find((item) => item.name === props.tool);
        codeDescription.value = listed?.description ?? null;

        const [showResponse, versionsResponse] = await Promise.all([
            tools.description(props.tool),
            tools.descriptionVersions(props.tool),
        ]);

        description.value = showResponse.data;
        versions.value = versionsResponse.data;
    } catch (e) {
        if (e instanceof ApiError && e.status === 404) {
            description.value = null;
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
        await tools.publishDescriptionVersion(props.tool, version);
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
        await tools.createDescriptionVersion(props.tool, {
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
        await tools.destroyDescription(props.tool);
        success.value = 'Override removed. The code-declared description is live again.';
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
            <h1><code>{{ tool }}</code> description</h1>
            <router-link class="button" :to="{ name: 'tools.index' }">Back to tools</router-link>
        </div>
        <Alert :message="error" />
        <Alert type="success" :message="success" />
        <Spinner v-if="loading" />
        <template v-else>
            <h2>Live description</h2>
            <p>
                {{ effectiveDescription }}
                <span v-if="description?.published_version" class="badge">override v{{ description.published_version }}</span>
                <span v-else class="badge">from code</span>
            </p>
            <p v-if="hasOverride && description.published_version" class="muted">
                Code-declared description stays dormant until the override is removed.
            </p>
            <p v-else-if="hasOverride" class="muted">
                Draft versions exist but none is published — the code-declared description is live.
            </p>

            <h2>Versions</h2>
            <p v-if="!versions.length" class="muted">No versions yet. Create one below to override the description.</p>
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
                                <span v-if="description?.published_version === version.version" class="badge">published</span>
                            </td>
                            <td class="muted">{{ new Date(version.created_at).toLocaleString() }}</td>
                            <td>
                                <div class="actions">
                                    <button type="button" @click="expanded = expanded === version.version ? null : version.version">
                                        {{ expanded === version.version ? 'Hide' : 'View' }}
                                    </button>
                                    <button
                                        v-if="description?.published_version !== version.version"
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
                    <label for="description-content">Content</label>
                    <textarea id="description-content" v-model="newVersion.content" class="code" required></textarea>
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
                <p class="muted">Deletes the override and its whole version history; the code-declared description takes over.</p>
                <ConfirmButton label="Remove override" @confirm="removeOverride" />
            </template>
        </template>
    </div>
</template>
