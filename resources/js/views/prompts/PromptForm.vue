<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import prompts from '../../api/prompts';
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
    content: '',
    publish: true,
});
const errors = ref({});
const error = ref(null);
const loading = ref(editing);
const saving = ref(false);

onMounted(async () => {
    if (!editing) {
        return;
    }

    try {
        const { data } = await prompts.show(props.slug);

        form.value.name = data.name;
        form.value.slug = data.slug;
        form.value.description = data.description ?? '';
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
});

async function save() {
    saving.value = true;
    error.value = null;
    errors.value = {};

    try {
        if (editing) {
            await prompts.update(props.slug, {
                name: form.value.name,
                description: form.value.description || null,
            });
            router.push({ name: 'prompts.show', params: { slug: props.slug } });
        } else {
            const { data } = await prompts.create({
                name: form.value.name,
                slug: form.value.slug,
                description: form.value.description || null,
                content: form.value.content,
                publish: form.value.publish,
            });
            router.push({ name: 'prompts.show', params: { slug: data.slug } });
        }
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
            <h1>{{ editing ? 'Edit prompt' : 'New prompt' }}</h1>
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
            <div v-if="!editing" class="field">
                <label for="content">Content</label>
                <textarea id="content" v-model="form.content" class="code" required></textarea>
                <FieldErrors :errors="errors" field="content" />
            </div>
            <div v-if="!editing" class="field">
                <label>
                    <input v-model="form.publish" type="checkbox" style="width: auto" />
                    Publish this version immediately
                </label>
                <FieldErrors :errors="errors" field="publish" />
            </div>
            <p v-else class="hint">Prompt content is versioned — add a new version from the prompt page.</p>
            <button class="primary" type="submit" :disabled="saving">
                {{ saving ? 'Saving…' : 'Save' }}
            </button>
        </form>
    </div>
</template>
