<script setup>
import { ref } from 'vue';

defineProps({
    label: { type: String, default: 'Delete' },
});

const emit = defineEmits(['confirm']);

const arming = ref(false);
let timer = null;

function click() {
    if (arming.value) {
        clearTimeout(timer);
        arming.value = false;
        emit('confirm');

        return;
    }

    arming.value = true;
    timer = setTimeout(() => (arming.value = false), 3000);
}
</script>

<template>
    <button type="button" class="danger" @click="click">
        {{ arming ? 'Confirm?' : label }}
    </button>
</template>
