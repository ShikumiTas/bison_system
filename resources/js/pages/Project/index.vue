<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { route } from 'ziggy-js';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

import { Head, useForm, } from "@inertiajs/vue3";
import { ref, } from "vue";

const props = defineProps({
  projects:           Object,
});

// submit 処理
function submit() {
}

// フォーム変数定義
const form = useForm({
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: '案件一覧',
        href: '/project/index',
    },
];

</script>

<template>
    <Head title="案件一覧" />
    <AppLayout :breadcrumbs="breadcrumbs">
        {{ projects }}
    </AppLayout>

</template>
