<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

import { Head, useForm, } from "@inertiajs/vue3";
import { ref, } from "vue";

const props = defineProps({
});

const form = useForm({});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: '経審・企業情報',
        // href: dashboard().url,
    },
];

</script>

<template>
    <Head title="経審・企業情報" />
    <AppLayout :breadcrumbs="breadcrumbs">  
    </AppLayout>

</template>
