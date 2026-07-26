<script setup>
import { onMounted, ref } from 'vue';
import agents from '../../api/agents';
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
        const response = await agents.list(page);

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
        await agents.destroy(slug);
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
            <h1>Agents</h1>
            <router-link class="button primary" :to="{ name: 'agents.create' }">New agent</router-link>
        </div>
        <Alert :message="error" />
        <Spinner v-if="loading" />
        <p v-else-if="!items.length" class="muted">No agents yet.</p>
        <table v-else>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Provider</th>
                    <th>Model</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="agent in items" :key="agent.slug">
                    <td>
                        <router-link :to="{ name: 'agents.edit', params: { slug: agent.slug } }">
                            {{ agent.name }}
                        </router-link>
                    </td>
                    <td class="muted">{{ agent.slug }}</td>
                    <td class="muted">{{ agent.provider ?? '—' }}</td>
                    <td class="muted">{{ agent.model ?? '—' }}</td>
                    <td>
                        <div class="actions">
                            <router-link class="button" :to="{ name: 'run', query: { agent: agent.slug } }">
                                Run
                            </router-link>
                            <ConfirmButton @confirm="destroy(agent.slug)" />
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <Pagination :meta="meta" @change="load" />
    </div>
</template>
