<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from "@inertiajs/vue3";
import { ref } from "vue";
import { type BreadcrumbItem } from '@/types';
import { 
    AlertCircle, 
    CheckCircle2, 
    UploadCloud, 
    XCircle, 
    ChevronRight,
    Loader2,
    Database,
    FileSpreadsheet,
    ShieldCheck
} from 'lucide-vue-next';

const props = defineProps<{
    flash: {
        message: string | null;
        temp_file_path?: string | null;
        import_type?: string | null;
        import_results?: {
            total_count: number;
            error_count: number;
            details: Array<{
                line: number;
                status: 'error';
                message: string;
             }>;
        } | null;
    };
}>();

const form = useForm({
    csv_file: null,
    type: 'project' 
});

const executing = ref(false);

const submit = () => {
    form.post('/import', {
        forceFormData: true,
        preserveState: true,
    });
};

const executeImport = () => {
    if (!props.flash.temp_file_path) return;
    executing.value = true;
    
    router.post('/import', {
        mode: 'execute',
        temp_path: props.flash.temp_file_path,
        type: props.flash.import_type,
    }, {
        onFinish: () => executing.value = false
    });
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: '外部データ取り込み', href: '/import' },
];
</script>

<template>
    <Head title="外部データ取り込み" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-zinc-50/50 dark:bg-zinc-950/50 p-4 md:p-8">
            <div class="max-w-3xl mx-auto space-y-6">
                
                <div class="flex flex-col gap-1 mb-2">
                    <h1 class="text-2xl font-black tracking-tight text-foreground flex items-center gap-2">
                        <Database class="w-6 h-6 text-indigo-600" />
                        データインポート
                    </h1>
                    <p class="text-sm text-muted-foreground">CSVファイルをアップロードして、一括登録または更新を行います。</p>
                </div>

                <div class="bg-card border border-border rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-6 md:p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="grid gap-2">
                                <label class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 flex items-center gap-2">
                                    <ShieldCheck class="w-3.5 h-3.5" /> データ種別
                                </label>
                                <select 
                                    v-model="form.type" 
                                    class="w-full h-11 text-sm border-zinc-200 dark:border-zinc-800 bg-background rounded-xl px-4 font-bold focus:ring-2 ring-indigo-500/20 outline-none transition-all cursor-pointer"
                                >
                                    <option value="project">案件データ (NJSS形式)</option>
                                    <option value="biz">企業データ (経審形式)</option>
                                </select>
                            </div>

                            <div class="grid gap-2">
                                <label class="text-[10px] font-bold uppercase tracking-widest text-zinc-500 flex items-center gap-2">
                                    <FileSpreadsheet class="w-3.5 h-3.5" /> CSVファイル
                                </label>
                                <div class="relative group">
                                    <input 
                                        type="file" 
                                        @input="(e: any) => form.csv_file = e.target.files[0]" 
                                        class="block w-full text-[11px] text-muted-foreground file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-[11px] file:font-black file:bg-zinc-100 dark:file:bg-zinc-800 file:text-zinc-600 dark:file:text-zinc-400 hover:file:bg-indigo-50 border border-zinc-200 dark:border-zinc-800 p-1 rounded-xl h-11 flex items-center"
                                    />
                                </div>
                            </div>
                        </div>

                        <div v-if="form.errors.csv_file" class="p-3 bg-destructive/5 rounded-lg border border-destructive/10 text-[11px] text-destructive font-bold flex items-center gap-2 animate-in fade-in">
                            <AlertCircle class="w-4 h-4" /> {{ form.errors.csv_file }}
                        </div>

                        <button 
                            @click="submit" 
                            :disabled="form.processing || !form.csv_file" 
                            class="w-full h-12 bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 font-black rounded-xl hover:opacity-90 active:scale-[0.98] transition-all disabled:opacity-30 text-sm flex items-center justify-center gap-2 shadow-xl shadow-zinc-200/50 dark:shadow-none"
                        >
                            <Loader2 v-if="form.processing" class="w-5 h-5 animate-spin" />
                            <UploadCloud v-else class="w-5 h-5" />
                            {{ form.processing ? 'ファイルを解析しています...' : '整合性を確認する' }}
                        </button>
                    </div>
                </div>

                <div v-if="props.flash?.import_results" class="animate-in slide-in-from-bottom-4 duration-500">
                    <div class="space-y-4">
                        <div class="bg-card border border-border rounded-2xl p-6 md:p-10 shadow-sm flex flex-col md:flex-row items-center justify-between gap-8 border-l-8 border-l-indigo-500">
                            <div class="text-center md:text-left">
                                <label class="text-[10px] font-bold uppercase tracking-[0.2em] text-zinc-500 block mb-2">読み取り成功件数</label>
                                <div class="text-5xl font-black text-foreground tracking-tighter">
                                    {{ props.flash.import_results.total_count.toLocaleString() }}
                                    <span class="text-sm font-normal text-zinc-400 ml-1">records</span>
                                </div>
                                <div v-if="props.flash?.message" class="mt-2 text-xs font-bold text-indigo-600 flex items-center justify-center md:justify-start gap-1">
                                    <CheckCircle2 class="w-3.5 h-3.5" /> {{ props.flash.message }}
                                </div>
                            </div>
                            
                            <div v-if="props.flash.import_results.error_count === 0" class="w-full md:w-auto">
                                <button 
                                    @click="executeImport"
                                    :disabled="executing"
                                    class="w-full md:w-auto px-10 py-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black text-lg shadow-2xl shadow-indigo-200 dark:shadow-none transition-all flex items-center justify-center gap-3 active:scale-95 disabled:opacity-50"
                                >
                                    <span v-if="!executing">インポートを実行</span>
                                    <Loader2 v-else class="w-6 h-6 animate-spin" />
                                    <ChevronRight v-if="!executing" class="w-6 h-6" />
                                </button>
                                <p class="mt-4 text-[11px] text-center text-muted-foreground italic">
                                    ※ 大規模なデータの場合、バックエンドで非同期に処理が行われ、完了まで数分かかる場合があります。
                                </p>
                            </div>
                        </div>

                        <div v-if="props.flash.import_results.error_count > 0" class="bg-card border border-destructive/20 rounded-2xl overflow-hidden shadow-md">
                            <div class="p-4 bg-destructive text-white flex items-center gap-2 font-black text-sm">
                                <XCircle class="w-5 h-5" />
                                {{ props.flash.import_results.error_count }} 件のエラーが見つかりました（ファイルを修正してください）
                            </div>
                            <div class="max-h-[400px] overflow-y-auto bg-zinc-50 dark:bg-zinc-900/50">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="border-b border-border/50 text-[10px] font-bold text-zinc-500 uppercase tracking-widest">
                                            <th class="px-6 py-3 w-20">行</th>
                                            <th class="px-6 py-3">エラー内容</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border/30">
                                        <tr v-for="err in props.flash.import_results.details" :key="err.line" class="hover:bg-muted/50 transition-colors">
                                            <td class="px-6 py-3 text-xs font-black text-destructive/70">{{ err.line }}</td>
                                            <td class="px-6 py-3 text-xs text-muted-foreground font-medium">{{ err.message }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else-if="!form.processing" class="py-20 flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-800 rounded-3xl text-zinc-400 bg-white/30 dark:bg-zinc-900/30">
                    <div class="p-6 bg-background rounded-full shadow-inner mb-6">
                        <UploadCloud class="w-12 h-12 opacity-10" />
                    </div>
                    <h3 class="text-lg font-bold text-foreground mb-2">CSVファイルを待機中</h3>
                    <p class="text-sm text-zinc-500 text-center max-w-sm px-6">
                        適切なデータ種別を選択し、ファイルをアップロードしてください。<br>インポート前に整合性の自動チェックが走ります。
                    </p>
                </div>

            </div>
        </div>
    </AppLayout>
</template>