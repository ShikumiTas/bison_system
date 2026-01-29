<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import { type BreadcrumbItem } from '@/types';

const props = defineProps<{
    projects?: any;
    flash: {
        message: string | null;
    };
}>();

const form = useForm({
    csv_file: null,
    type: 'projects' // 'projects' or 'bizs'
});

const submit = () => {
    form.post('/import', {
        forceFormData: true,
        onSuccess: () => alert('サーバーに送信しました。バックグラウンドでインポート中です。'),
    });
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: '外部データ取り込み',
        href: '/import',
    },
];

</script>

<template>
        <Head title="外部データ取り込み" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 lg:p-8 bg-white dark:bg-zinc-950 min-h-screen transition-colors duration-300">
            <div class="max-w-xl">
                <header class="mb-6">
                    <h2 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">外部データ取り込み</h2>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        CSVファイルをアップロードして、案件や企業の情報を一括更新します。
                    </p>
                </header>
                <div v-if="props.flash?.message" class="mb-6 p-4 text-sm text-green-600 bg-green-50 dark:bg-green-900/20 dark:text-green-400 rounded-lg border border-green-200 dark:border-green-800/30">
                    {{ props.flash.message }}
                </div>
                <div v-if="form.errors.csv_file" class="mt-2 text-sm text-red-500">
                    {{ form.errors.csv_file }}
                </div>

                <div v-if="form.errors.type" class="mt-2 text-sm text-red-500">
                    {{ form.errors.type }}
                </div>
               <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">データ種別</label>
                        <select v-model="form.type" 
                            class="w-full rounded-lg border-zinc-300 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-200 focus:ring-indigo-500">
                            <option value="projects">案件データ (projects)</option>
                            <option value="bizs">企業データ (bizs)</option>
                        </select>
                    </div>

                    <div class="p-4 bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg">
                        <input 
                            type="file" 
                            @input="(e: any) => form.csv_file = e.target.files[0]"
                            class="block w-full text-sm text-zinc-500 
                                   file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 
                                   file:text-sm file:font-semibold 
                                   file:bg-white dark:file:bg-zinc-800 
                                   file:text-zinc-700 dark:file:text-zinc-300 
                                   hover:file:bg-zinc-100 dark:hover:file:bg-zinc-700"
                        />
                    </div>

                    <button 
                        @click="submit" 
                        :disabled="form.processing"
                        class="px-4 py-2 bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 font-bold rounded-md hover:opacity-90 transition disabled:opacity-50"
                    >
                        {{ form.processing ? '送信中...' : 'インポートを開始' }}
                    </button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>