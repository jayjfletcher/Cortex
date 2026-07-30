<script setup>
import { onMounted, ref } from 'vue';
import tools from '../api/tools';
import Alert from '../components/Alert.vue';
import Spinner from '../components/Spinner.vue';

const items = ref([]);
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
    try {
        items.value = (await tools.list()).data;
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div>
        <div class="page-header">
            <h1>Tools</h1>
        </div>
        <Alert :message="error" />
        <Spinner v-if="loading" />
        <p v-else-if="!items.length" class="muted">No tools registered. Add tool classes to the <code>cortex.tools</code> config.</p>
        <table v-else>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Schema</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="tool in items" :key="tool.name">
                    <td>{{ tool.name }}</td>
                    <td>{{ tool.description }}</td>
                    <td><pre>{{ JSON.stringify(tool.schema, null, 2) }}</pre></td>
                    <td>
                        <router-link class="button" :to="{ name: 'tools.description', params: { tool: tool.name } }">
                            Description
                        </router-link>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
