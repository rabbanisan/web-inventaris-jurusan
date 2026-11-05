<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    links: Array,
});
</script>

<template>
    <nav class="flex items-center space-x-2">
        <template v-for="link in links" :key="link.label">
            <Link
                v-if="!link.active && link.url"
                :href="link.url"
                class="px-3 py-1 border rounded hover:bg-gray-100"
                v-html="link.label"
            />
            <span
                v-else
                class="px-3 py-1 border rounded bg-gray-200 font-medium"
                v-html="link.label"
            />
        </template>
    </nav>
</template>
