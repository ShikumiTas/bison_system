<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from "@inertiajs/vue3";
import {
    Search, Building2, Globe, ChevronRight, ChevronLeft,
    Clock, ShieldCheck, Tag, Users, MoreHorizontal
} from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const props = defineProps({
    projects: {
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
    filters: Object,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: '案件一覧', href: '/project/index' },
];

const form = useForm({
    keyword: props.filters?.keyword || '',
    status: props.filters?.status || '',
});

const formatCurrency = (value: number | null) => {
    if (!value) return '---';
    return new Intl.NumberFormat('ja-JP', { style: 'currency', currency: 'JPY' }).format(value);
};

const formatDate = (dateStr: string | null) => {
    if (!dateStr) return '-';
    return dateStr.split('T')[0].replaceAll('-', '/');
};

/**
 * ペジネーションラベルの正規化
 */
const getLinkLabel = (label: string) => {
    if (label.includes('Previous') || label.includes('previous')) return 'prev';
    if (label.includes('Next') || label.includes('next')) return 'next';
    return label;
};

const getStatusConfig = (project: any) => {
    const statusMap: Record<number, { label: string; class: string }> = {
        0: { label: '検討中', class: 'bg-slate-500/15 text-slate-600 dark:text-slate-300 border-slate-500/30' },
        1: { label: '調査・準備', class: 'bg-blue-500/15 text-blue-700 dark:text-blue-300 border-blue-500/30' },
        2: { label: '応札済', class: 'bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border-indigo-500/30' },
        3: { label: '落札', class: 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border-emerald-500/30' },
        4: { label: '辞退・失注', class: 'bg-rose-500/15 text-rose-700 dark:text-rose-300 border-rose-500/30' },
        5: { label: '完了', class: 'bg-amber-500/15 text-amber-700 dark:text-amber-200 border-amber-500/30' },
    };
    return statusMap[project.status] || (project.matched_bizs_count > 0 ? statusMap[1] : statusMap[0]);
};

function submit() {
    form.get('/project/index', { preserveState: true, preserveScroll: true });
}
</script>

<template>

    <Head title="案件一覧" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6 text-left">

            <div
                class="mb-6 flex flex-col md:flex-row gap-3 items-stretch md:items-end bg-card p-4 rounded-xl border shadow-sm text-left">
                <div class="flex-1">
                    <label
                        class="text-[10px] font-bold text-muted-foreground mb-1 block ml-1 tracking-widest uppercase text-left">案件検索</label>
                    <Input v-model="form.keyword" placeholder="案件名・顧客名..." class="h-10 text-sm font-medium"
                        @keyup.enter="submit" />
                </div>
                <div class="flex gap-2">
                    <div class="flex-1 md:w-48">
                        <label
                            class="md:hidden text-[10px] font-bold text-muted-foreground mb-1 block ml-1">ステータス</label>
                        <select v-model="form.status"
                            class="w-full h-10 rounded-md border border-input bg-background px-3 text-sm font-bold focus:ring-2 focus:ring-primary/20 outline-none">
                            <option value="">全ステータス</option>
                            <option v-for="i in [0, 1, 2, 3, 4, 5]" :key="i" :value="i">{{ getStatusConfig({ status: i }).label
                                }}</option>
                        </select>
                    </div>
                    <Button @click="submit" class="h-10 px-6 font-bold shadow-sm self-end md:self-auto">
                        <Search class="w-4 h-4 md:mr-2" />
                        <span class="hidden md:inline">検索する</span>
                    </Button>
                </div>
            </div>

            <div class="rounded-xl border bg-card shadow-md overflow-hidden">
                <table class="w-full text-sm hidden md:table">
                    <thead class="bg-muted/50 border-b text-muted-foreground">
                        <tr>
                            <th class="h-12 px-5 text-left font-bold text-[11px]">案件・発注機関</th>
                            <th class="h-12 px-4 text-left font-bold text-[11px] w-[130px]">ステータス</th>
                            <th class="h-12 px-4 text-left font-bold text-[11px] w-[220px]">スペック / 業種</th>
                            <th class="h-12 px-4 text-left font-bold text-[11px] w-[180px]">期限 / 参画</th>
                            <th class="h-12 px-4 text-right font-bold text-[11px] w-[160px]">想定金額</th>
                            <th class="h-12 px-5 text-right font-bold text-[11px] w-[80px]">詳細</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="project in projects?.data" :key="project.id"
                            class="hover:bg-muted/5 transition-colors group">
                            <td class="p-5 align-top text-left">
                                <div
                                    class="font-bold text-foreground leading-snug mb-2 text-[15px] group-hover:text-primary transition-colors text-left">
                                    {{ project.title }}
                                </div>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-muted/50 text-muted-foreground border border-border text-[10px] font-bold">
                                        <Building2 class="w-3 h-3 opacity-70" />
                                        {{ project.organization?.replace('機関名：', '') || '発注者不明' }}
                                    </div>
                                    <a v-if="project.url" :href="project.url" target="_blank"
                                        class="text-blue-500 dark:text-blue-400 hover:underline flex items-center gap-1 text-[10px] font-medium transition-colors">
                                        <Globe class="w-3 h-3" /> サイト
                                    </a>
                                </div>
                            </td>

                            <td class="p-4 align-top">
                                <span
                                    :class="['px-3 py-1.5 rounded text-[11px] font-bold border shadow-sm block text-center', getStatusConfig(project).class]">
                                    {{ getStatusConfig(project).label }}
                                </span>
                            </td>

                            <td class="p-4 align-top text-left">
                                <div class="flex flex-col gap-1.5">
                                    <div v-if="project.bidding_qualifications"
                                        class="flex items-center gap-1.5 text-foreground/80 text-[11px] font-bold">
                                        <ShieldCheck class="w-3.5 h-3.5 text-slate-400" />
                                        <span class="truncate max-w-[150px]">{{ project.bidding_qualifications }}</span>
                                    </div>
                                    <div v-if="project.industry"
                                        class="flex items-center gap-1.5 text-muted-foreground text-[11px] font-medium">
                                        <Tag class="w-3.5 h-3.5 opacity-40" />
                                        <span class="truncate max-w-[150px]">{{ project.industry }}</span>
                                    </div>
                                </div>
                            </td>

                            <td class="p-4 align-top text-left">
                                <div class="flex flex-col gap-2">
                                    <div
                                        class="flex items-center gap-2 font-bold text-orange-600 dark:text-orange-400 text-[11px]">
                                        <Clock class="w-3.5 h-3.5" />
                                        <span>入札: {{ formatDate(project.bid_date) }}</span>
                                    </div>
                                    <div v-if="project.matched_bizs_count > 0"
                                        class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-600 dark:text-indigo-300 border border-indigo-500/20 text-[10px] font-bold w-fit">
                                        <Users class="w-3 h-3" /> 参画 {{ project.matched_bizs_count }} 社
                                    </div>
                                </div>
                            </td>

                            <td class="p-4 text-right align-top tabular-nums">
                                <span
                                    class="text-[9px] font-bold text-muted-foreground uppercase leading-none mb-1 block tracking-tighter italic">想定金額</span>
                                <span class="font-mono font-bold text-[16px] text-foreground">{{
                                    formatCurrency(project.expected_amount) }}</span>
                            </td>

                            <td class="p-5 text-right align-middle">
                                <Button variant="ghost" size="icon" as-child
                                    class="rounded-full hover:bg-primary hover:text-white transition-all shadow-sm border h-8 w-8">
                                    <Link :href="`/project/edit/${project.id}`">
                                        <ChevronRight class="w-4 h-4" />
                                    </Link>
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="md:hidden divide-y">
                    <div v-for="project in projects?.data" :key="project.id" class="relative group">
                        <Link :href="`/project/edit/${project.id}`"
                            class="block p-5 active:bg-muted/50 space-y-4 text-left">
                            <div class="flex justify-between items-start">
                                <span
                                    class="text-[10px] font-mono text-muted-foreground bg-muted px-1.5 py-0.5 rounded">#{{
                                    project.id }}</span>
                                <span
                                    :class="['px-2.5 py-1 rounded text-[10px] font-bold border', getStatusConfig(project).class]">
                                    {{ getStatusConfig(project).label }}
                                </span>
                            </div>

                            <div class="font-black text-base text-foreground leading-tight">{{ project.title }}</div>

                            <div class="flex flex-wrap gap-2">
                                <div
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-muted/50 text-muted-foreground border text-[10px] font-bold italic">
                                    <Building2 class="w-3 h-3" /> {{ project.organization?.replace('機関名：', '') ||
                                    '発注者不明' }}
                                </div>
                                <div v-if="project.matched_bizs_count > 0"
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-600 border border-indigo-500/20 text-[10px] font-bold">
                                    <Users class="w-3 h-3" /> 参画 {{ project.matched_bizs_count }}社
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 pt-3 border-t border-dashed">
                                <div class="text-[10px] space-y-1 font-bold">
                                    <div class="text-muted-foreground italic uppercase flex items-center gap-1">
                                        <Clock class="w-3 h-3" /> 入札日
                                    </div>
                                    <div class="text-orange-600 font-black pl-4">{{ formatDate(project.bid_date) }}
                                    </div>
                                </div>
                                <div class="text-[10px] space-y-1 text-right font-bold">
                                    <div class="text-muted-foreground italic uppercase">想定金額</div>
                                    <div class="text-foreground text-sm font-mono">{{
                                        formatCurrency(project.expected_amount) }}</div>
                                </div>
                            </div>

                            <div
                                class="flex items-center justify-end text-primary font-black text-[10px] uppercase tracking-widest pt-1">
                                案件詳細を表示
                                <ChevronRight class="w-3.5 h-3.5" />
                            </div>
                        </Link>
                    </div>
                </div>

                <div v-if="projects?.links?.length > 3"
                    class="px-5 py-4 border-t bg-muted/10 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="text-[11px] font-bold text-muted-foreground tracking-widest uppercase">
                        全 {{ projects.total }} 件中 {{ projects.from }} 〜 {{ projects.to }} 件
                    </div>
                    <nav class="flex items-center gap-1">
                        <template v-for="(link, key) in projects.links" :key="key">
                            <Button v-if="getLinkLabel(link.label) === 'prev'" variant="ghost" size="sm" as-child
                                class="h-8 px-2 md:w-8 md:p-0" :class="!link.url && 'pointer-events-none opacity-20'">
                                <Link :href="link.url || '#'" preserve-scroll>
                                    <ChevronLeft class="h-4 w-4" /><span
                                        class="md:hidden text-[10px] ml-1 font-bold">前へ</span>
                                </Link>
                            </Button>

                            <Button v-else-if="getLinkLabel(link.label) === 'next'" variant="ghost" size="sm" as-child
                                class="h-8 px-2 md:w-8 md:p-0" :class="!link.url && 'pointer-events-none opacity-20'">
                                <Link :href="link.url || '#'" preserve-scroll><span
                                        class="md:hidden text-[10px] mr-1 font-bold">次へ</span>
                                    <ChevronRight class="h-4 w-4" />
                                </Link>
                            </Button>

                            <Button v-else-if="link.label !== '...'" :variant="link.active ? 'outline' : 'ghost'"
                                size="sm" as-child class="h-8 w-8 p-0 text-[11px] font-bold" :class="[
                                    link.active ? 'bg-background shadow-sm border-primary/20 text-primary font-black' : 'text-muted-foreground font-medium',
                                    !link.active && !['1', String(projects.last_page)].includes(link.label) && Math.abs(Number(link.label) - projects.current_page) > 1 ? 'hidden md:flex' : 'flex'
                                ]">
                                <Link :href="link.url || '#'" preserve-scroll>{{ link.label }}</Link>
                            </Button>

                            <span v-else
                                class="hidden md:flex h-8 w-8 items-center justify-center text-muted-foreground/30">
                                <MoreHorizontal class="h-4 w-4" />
                            </span>
                        </template>
                    </nav>
                </div>
            </div>
        </div>
    </AppLayout>
</template>