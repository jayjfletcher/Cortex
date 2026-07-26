<script setup>
defineProps({
    meta: { type: Object, default: null },
});

defineEmits(['change']);
</script>

<template>
    <div v-if="meta && meta.last_page > 1" class="pagination">
        <button type="button" :disabled="meta.current_page <= 1" @click="$emit('change', meta.current_page - 1)">
            Previous
        </button>
        <span>Page {{ meta.current_page }} of {{ meta.last_page }}</span>
        <button type="button" :disabled="meta.current_page >= meta.last_page" @click="$emit('change', meta.current_page + 1)">
            Next
        </button>
    </div>
</template>
