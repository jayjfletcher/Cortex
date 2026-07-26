<script setup>
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import agents from '../api/agents';
import fetchAllPages from '../api/paginate';
import { ApiError } from '../api/client';
import Alert from '../components/Alert.vue';
import FieldErrors from '../components/FieldErrors.vue';
import Spinner from '../components/Spinner.vue';

const route = useRoute();

const agentOptions = ref([]);
const selected = ref('');
const input = ref('');
const result = ref(null);
const errors = ref({});
const error = ref(null);
const loading = ref(true);
const running = ref(false);

onMounted(async () => {
    try {
        agentOptions.value = await fetchAllPages(agents.list);

        const requested = route.query.agent;

        if (requested && agentOptions.value.some((agent) => agent.slug === requested)) {
            selected.value = requested;
        }
    } catch (e) {
        error.value = e.message;
    } finally {
        loading.value = false;
    }
});

async function run() {
    running.value = true;
    error.value = null;
    errors.value = {};
    result.value = null;

    try {
        result.value = (await agents.run(selected.value, input.value)).data;
    } catch (e) {
        if (e instanceof ApiError && e.status === 422) {
            errors.value = e.errors;
        } else {
            error.value = e.message;
        }
    } finally {
        running.value = false;
    }
}
</script>

<template>
    <div>
        <div class="page-header">
            <h1>Run agent</h1>
        </div>
        <Alert :message="error" />
        <Spinner v-if="loading" />
        <template v-else>
            <p v-if="!agentOptions.length" class="muted">No agents to run yet.</p>
            <form v-else @submit.prevent="run">
                <div class="field">
                    <label for="agent">Agent</label>
                    <select id="agent" v-model="selected" required>
                        <option value="" disabled>Select an agent</option>
                        <option v-for="agent in agentOptions" :key="agent.slug" :value="agent.slug">
                            {{ agent.name }} ({{ agent.slug }})
                        </option>
                    </select>
                </div>
                <div class="field">
                    <label for="input">Input</label>
                    <textarea id="input" v-model="input" required></textarea>
                    <FieldErrors :errors="errors" field="input" />
                </div>
                <button class="primary" type="submit" :disabled="running || !selected">
                    {{ running ? 'Running…' : 'Run' }}
                </button>
            </form>

            <template v-if="result">
                <h2>Response</h2>
                <pre>{{ result.text }}</pre>
                <table v-if="result.usage" class="usage-table">
                    <thead>
                        <tr>
                            <th>Usage</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(value, key) in result.usage" :key="key">
                            <td class="muted">{{ key }}</td>
                            <td>{{ value }}</td>
                        </tr>
                    </tbody>
                </table>
            </template>
        </template>
    </div>
</template>
