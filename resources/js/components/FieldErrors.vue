<script setup>
defineProps({
    errors: { type: Object, default: () => ({}) },
    field: { type: String, required: true },
});
</script>

<template>
    <ul v-if="errors[field]?.length" class="field-errors">
        <li v-for="message in errors[field]" :key="message">{{ message }}</li>
    </ul>
</template>
