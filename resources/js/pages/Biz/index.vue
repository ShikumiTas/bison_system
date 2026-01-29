<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

import { Head, useForm, } from "@inertiajs/vue3";
import { ref, } from "vue";

const props = defineProps({
  post:           Object,
});

// submit 処理
function submit() {
}

// フォーム変数定義
const form = useForm({
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: '経審・企業一覧',
        href: '/biz/index',
    },
];

</script>

<template>
    <Head title="経審・企業一覧" />
    <AppLayout :breadcrumbs="breadcrumbs">
    </AppLayout>

</template>
