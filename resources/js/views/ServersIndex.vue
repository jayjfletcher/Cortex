<script setup>
import { onMounted, ref } from 'vue';
import servers from '../api/servers';
import Alert from '../components/Alert.vue';
import Spinner from '../components/Spinner.vue';

const items = ref([]);
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
    try {
        items.value = (await servers.list()).data;
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
            <h1>Servers</h1>
        </div>
        <Alert :message="error" />
        <Spinner v-if="loading" />
        <p v-else-if="!items.length" class="muted">No MCP servers registered. Add server classes to the <code>cortex.mcp.servers</code> config.</p>
        <table v-else>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Instructions</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="server in items" :key="server.name">
                    <td>{{ server.name }}</td>
                    <td>{{ server.instructions }}</td>
                    <td>
                        <router-link class="button" :to="{ name: 'servers.instructions', params: { server: server.name } }">
                            Instructions
                        </router-link>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
