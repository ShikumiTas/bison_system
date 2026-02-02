<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>
}>();
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap justify-center mt-6 gap-1">
        <template v-for="(link, key) in links" :key="key">
            <div
                v-if="link.url === null"
                class="px-3 py-2 text-sm leading-4 text-muted-foreground border rounded-md"
                v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                class="px-3 py-2 text-sm leading-4 border rounded-md transition-colors hover:bg-muted"
                :class="{ 'bg-primary text-primary-foreground hover:bg-primary/90': link.active }"
                v-html="link.label"
                preserve-scroll
            />
        </template>
    </div>
</template>