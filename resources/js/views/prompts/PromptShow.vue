<script setup>
import { onMounted, ref } from 'vue';
import prompts from '../../api/prompts';
import { ApiError } from '../../api/client';
import Alert from '../../components/Alert.vue';
import FieldErrors from '../../components/FieldErrors.vue';
import Pagination from '../../components/Pagination.vue';
import Spinner from '../../components/Spinner.vue';

const props = defineProps({
    slug: { type: String, required: true },
});

const prompt = ref(null);
const versions = ref([]);
const meta = ref(null);
const expanded = ref(null);
const loading = ref(true);
const error = ref(null);
const success = ref(null);

const newVersion = ref({ content: '', publish: false });
const savingVersion = ref(false);
const errors = ref({});

async function load(page = 1) {
    loading.value = true;
    error.value = null;

    try {
        const [showResponse, versionsResponse] = await Promise.all([
            prompts.show(props.slug),
            prompts.versions(props.slug, page),
        ]);

        prompt.value = showResponse.data;
        versions.value = versionsResponse.data;
        meta.value = versionsResponse.meta;
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
}

async function publish(version) {
    error.value = null;
    success.value = null;

    try {
        await prompts.publishVersion(props.slug, version);
        success.value = `Version ${version} published.`;
        await load(meta.value?.current_page ?? 1);
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
        await prompts.createVersion(props.slug, {
            content: newVersion.value.content,
            publish: newVersion.value.publish,
        });
        newVersion.value = { content: '', publish: false };
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

onMounted(load);
</script>

<template>
    <div>
        <Spinner v-if="loading && !prompt" />
        <template v-else-if="prompt">
            <div class="page-header">
                <h1>{{ prompt.name }}</h1>
                <router-link class="button" :to="{ name: 'prompts.edit', params: { slug: prompt.slug } }">
                    Edit
                </router-link>
            </div>
            <Alert :message="error" />
            <Alert type="success" :message="success" />
            <p class="muted">
                <code>{{ prompt.slug }}</code>
                <template v-if="prompt.description"> — {{ prompt.description }}</template>
            </p>

            <h2>Versions</h2>
            <p v-if="!versions.length" class="muted">No versions yet.</p>
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
                                <span v-if="prompt.published_version?.version === version.version" class="badge">published</span>
                            </td>
                            <td class="muted">{{ new Date(version.created_at).toLocaleString() }}</td>
                            <td>
                                <div class="actions">
                                    <button type="button" @click="expanded = expanded === version.version ? null : version.version">
                                        {{ expanded === version.version ? 'Hide' : 'View' }}
                                    </button>
                                    <button
                                        v-if="prompt.published_version?.version !== version.version"
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
            <Pagination :meta="meta" @change="load" />

            <h2>New version</h2>
            <form @submit.prevent="createVersion">
                <div class="field">
                    <label for="version-content">Content</label>
                    <textarea id="version-content" v-model="newVersion.content" class="code" required></textarea>
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
        </template>
        <Alert v-else :message="error" />
    </div>
</template>
