<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link, router } from "@inertiajs/vue3";
import {
    Search, Plus, ShieldCheck, ArrowRight, Clock, Tag, 
    Landmark, ChevronRight, Trash2, FileText, Hash 
} from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

const props = defineProps({
    qualifications: {
        type: Object,
        default: () => ({
            data: [],
            links: [],
            total: 0,
            from: 0,
            to: 0,
            current_page: 1,
            last_page: 1
        })
    },
    filters: {
        type: Object,
        default: () => ({})
    },
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: '入札参加資格一覧', href: '/qualification/index' },
];

const form = useForm({
    keyword: props.filters?.keyword ?? '',
});

function submit() {
    form.get('/qualification/index', { preserveState: true, preserveScroll: true });
}

function confirmDelete(id: number) {
    if (confirm('この資格を削除しますか？')) {
        router.delete(`/qualification/${id}`, { preserveScroll: true });
    }
}

/**
 * ランクに応じたスタイルを適用
 * DBの grade フィールド (A, B, C, D) に対応
 */
const getRankClass = (rank: string | null) => {
    if (!rank) return 'bg-slate-100 text-slate-500 border-slate-200';
    const map: Record<string, string> = {
        'A': 'bg-rose-500/10 text-rose-600 border-rose-200',
        'B': 'bg-amber-500/10 text-amber-600 border-amber-200',
        'C': 'bg-emerald-500/10 text-emerald-600 border-emerald-200',
        'D': 'bg-blue-500/10 text-blue-600 border-blue-200',
    };
    return map[rank.toUpperCase()] || 'bg-muted text-muted-foreground border-border';
};
</script>

<template>
    <Head title="入札参加資格一覧" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6 text-left">
            <div class="mb-6 flex flex-col md:flex-row gap-3 items-stretch md:items-end bg-card p-4 rounded-xl border shadow-sm">
                <div class="flex-1">
                    <label class="text-[10px] font-bold text-muted-foreground mb-1 block ml-1 tracking-widest uppercase italic">自社資格検索</label>
                    <div class="flex gap-2">
                        <div class="relative flex-1">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                            <Input v-model="form.keyword" placeholder="資格名・発行機関・業者コード..." class="h-10 pl-9 text-sm font-medium" @keyup.enter="submit" />
                        </div>
                        <Button @click="submit" class="h-10 px-6 font-bold shadow-sm">
                            検索
                        </Button>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border bg-card shadow-md overflow-hidden">
                <table class="w-full text-sm hidden md:table">
                    <thead class="bg-muted/50 border-b text-muted-foreground">
                        <tr>
                            <th class="h-12 px-5 text-left font-bold text-[11px] w-[380px] tracking-wider uppercase whitespace-nowrap">資格名称 / 発行機関</th>
                            <th class="h-12 px-4 text-left font-bold text-[11px] tracking-wider uppercase whitespace-nowrap">業種・等級（ランク）</th>
                            <th class="h-12 px-4 text-left font-bold text-[11px] w-[160px] tracking-wider uppercase whitespace-nowrap">有効期限</th>
                            <th class="h-12 px-5 text-right font-bold text-[11px] w-[180px] tracking-wider uppercase whitespace-nowrap">操作</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="q in qualifications?.data" :key="q.id" class="hover:bg-muted/5 transition-colors group">
                            <td class="p-5 align-top">
                                <div class="flex items-start gap-4">
                                    <div class="p-2.5 bg-primary/5 rounded-xl text-primary border border-primary/10 shrink-0 mt-1">
                                        <ShieldCheck class="w-5 h-5" />
                                    </div>
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-slate-800 text-white border border-slate-800 uppercase tracking-tighter">
                                                {{ q.authority || '未設定' }}
                                            </span>
                                            <span v-if="q.trader_code" class="text-[10px] text-muted-foreground font-mono font-bold bg-muted px-1.5 py-0.5 rounded flex items-center gap-1">
                                                <Hash class="w-3 h-3 opacity-50" /> {{ q.trader_code }}
                                            </span>
                                        </div>
                                        <div class="font-bold text-[15px] group-hover:text-primary transition-colors leading-snug">
                                            {{ q.name }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="p-5 align-top">
                                <div class="flex flex-col gap-2.5 mt-1">
                                    <template v-if="q.business_items && q.business_items.length">
                                        <div v-for="(item, index) in q.business_items" :key="index" class="flex items-center gap-2">
                                            <span :class="['px-1.5 py-0.5 rounded text-[10px] font-black border min-w-[1.8rem] text-center shadow-sm', getRankClass(item.grade)]">
                                                {{ item.grade || '-' }}
                                            </span>
                                            <span class="text-[12px] font-bold text-slate-700 whitespace-nowrap">
                                                {{ item.name }}
                                            </span>
                                            <span v-if="item.score" class="text-[10px] text-muted-foreground/50 font-mono italic">
                                                P={{ item.score }}
                                            </span>
                                        </div>
                                    </template>
                                    <div v-else class="text-[10px] text-muted-foreground/40 italic flex items-center gap-1">
                                        <Tag class="w-3 h-3" /> 業種未設定
                                    </div>
                                </div>
                            </td>

                            <td class="p-5 align-top text-left whitespace-nowrap">
                                <div class="inline-flex items-center gap-2 text-[12px] font-bold text-orange-600 mt-1.5 bg-orange-50 px-2.5 py-1.5 rounded-md border border-orange-100 shadow-sm">
                                    <Clock class="w-3.5 h-3.5 shrink-0" />
                                    <span class="tabular-nums">{{ q.expired_at }}</span>
                                </div>
                            </td>

                            <td class="p-5 text-right align-middle whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <Button v-if="q.pdf_url" variant="ghost" size="icon" as-child
                                        class="rounded-full hover:bg-blue-50 text-blue-500 border border-blue-100 transition-all"
                                        title="原本PDFを表示">
                                        <a :href="q.pdf_url" target="_blank">
                                            <FileText class="w-4 h-4" />
                                        </a>
                                    </Button>

                                    <Button variant="ghost" size="icon" @click="confirmDelete(q.id)"
                                        class="rounded-full hover:bg-red-50 text-red-400 hover:text-red-600 border border-transparent hover:border-red-100 transition-all"
                                        title="削除">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>

                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="md:hidden divide-y">
                    <div v-for="q in qualifications?.data" :key="q.id" class="p-5 space-y-4">
                        <div class="flex flex-col gap-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-slate-800 text-white border border-slate-800">
                                    {{ q.authority }}
                                </span>
                                <span v-if="q.trader_code" class="text-[9px] font-mono font-bold text-muted-foreground">
                                    #{{ q.trader_code }}
                                </span>
                            </div>
                            <div class="font-black text-[16px] leading-tight">{{ q.name }}</div>
                        </div>

                        <div v-if="q.business_items && q.business_items.length" class="flex flex-wrap gap-2">
                            <div v-for="(item, index) in q.business_items" :key="index" 
                                class="flex items-center gap-1.5 bg-slate-50 border border-slate-100 px-2 py-1 rounded shadow-sm">
                                <span v-if="item.grade" :class="['px-1 py-0.5 rounded text-[8px] font-black border min-w-[1.2rem] text-center', getRankClass(item.grade)]">
                                    {{ item.grade }}
                                </span>
                                <span class="text-[10px] font-bold text-slate-600">{{ item.name }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t border-dashed">
                            <div class="flex flex-col">
                                <span class="text-[8px] text-muted-foreground font-black uppercase tracking-tighter">有効期限</span>
                                <span class="text-[12px] font-bold text-orange-600 tabular-nums">{{ q.expired_at }}</span>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <Button variant="ghost" size="icon" @click="confirmDelete(q.id)" class="h-8 w-8 text-red-400">
                                    <Trash2 class="w-4 h-4" />
                                </Button>
                                <Button v-if="q.pdf_url" variant="outline" size="sm" as-child class="h-8 w-8 p-0 rounded-full border-blue-200 text-blue-600">
                                    <a :href="q.pdf_url" target="_blank">
                                        <FileText class="w-3.5 h-3.5" />
                                    </a>
                                </Button>
                                </div>
                        </div>
                    </div>
                </div>
                
                <div v-if="qualifications?.links?.length > 3" class="px-5 py-4 border-t bg-muted/10 flex items-center justify-between">
                    <div class="text-[10px] font-bold text-muted-foreground uppercase tracking-[0.2em]">
                        Total Records: {{ qualifications.total }}
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
<style scoped>
/* 必要に応じてテーブルのセル幅や改行を微調整 */
td {
    word-break: break-word;
}
</style>