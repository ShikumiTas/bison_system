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
        title: '案件詳細',
        href: '/project/edit',
    },
];

</script>

<template>
    <Head title="案件詳細" />
    <AppLayout :breadcrumbs="breadcrumbs">  
    </AppLayout>

</template>
