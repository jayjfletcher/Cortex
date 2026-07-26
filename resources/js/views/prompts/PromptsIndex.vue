<script setup>
import { onMounted, ref } from 'vue';
import prompts from '../../api/prompts';
import Alert from '../../components/Alert.vue';
import ConfirmButton from '../../components/ConfirmButton.vue';
import Pagination from '../../components/Pagination.vue';
import Spinner from '../../components/Spinner.vue';

const items = ref([]);
const meta = ref(null);
const loading = ref(true);
const error = ref(null);

async function load(page = 1) {
    loading.value = true;
    error.value = null;

    try {
        const response = await prompts.list(page);

        items.value = response.data;
        meta.value = response.meta;
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
}

async function destroy(slug) {
    error.value = null;

    try {
        await prompts.destroy(slug);
        await load(meta.value?.current_page ?? 1);
    } catch (e) {
        error.value = e.message;
    }
}

onMounted(load);
</script>

<template>
    <div>
        <div class="page-header">
            <h1>Prompts</h1>
            <router-link class="button primary" :to="{ name: 'prompts.create' }">New prompt</router-link>
        </div>
        <Alert :message="error" />
        <Spinner v-if="loading" />
        <p v-else-if="!items.length" class="muted">No prompts yet.</p>
        <table v-else>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Published</th>
                    <th>Updated</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="prompt in items" :key="prompt.slug">
                    <td>
                        <router-link :to="{ name: 'prompts.show', params: { slug: prompt.slug } }">
                            {{ prompt.name }}
                        </router-link>
                    </td>
                    <td class="muted">{{ prompt.slug }}</td>
                    <td>
                        <span v-if="prompt.published_version" class="badge">v{{ prompt.published_version.version }}</span>
                        <span v-else class="muted">—</span>
                    </td>
                    <td class="muted">{{ new Date(prompt.updated_at).toLocaleString() }}</td>
                    <td>
                        <div class="actions">
                            <router-link class="button" :to="{ name: 'prompts.edit', params: { slug: prompt.slug } }">
                                Edit
                            </router-link>
                            <ConfirmButton @confirm="destroy(prompt.slug)" />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <Pagination :meta="meta" @change="load" />
    </div>
</template>
