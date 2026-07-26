<script setup>
defineProps({
    type: { type: String, default: 'error' },
    message: { type: String, default: null },
});
</script>

<template>
    <div v-if="message" class="alert" :class="`alert-${type}`" role="alert">
        {{ message }}
    </div>
</template>
