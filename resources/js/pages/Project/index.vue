<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, Link } from "@inertiajs/vue3";
import { 
    Search, Building2, Globe, ArrowRight, 
    CalendarDays, Clock, ShieldCheck, Tag,
    User, DollarSign, ExternalLink, Users
} from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const props = defineProps({
    projects: Object,
});

const breadcrumbs = [
    { title: '案件一覧', href: '/project/index' },
];

const form = useForm({
    keyword: '',
    status: '',
});

// 金額フォーマット
const formatCurrency = (value: number | null) => {
    if (!value) return '---';
    return new Intl.NumberFormat('ja-JP', { style: 'currency', currency: 'JPY' }).format(value);
};

// 日付フォーマット
const formatDate = (dateStr: string | null) => {
    if (!dateStr) return '-';
    return dateStr.split('T')[0].replaceAll('-', '/');
};

// ステータスラベルと色の判定
const getStatusConfig = (project: any) => {
    // 完工判定ロジック（仮：statusカラムが2なら完工）
    if (project.status === 2) return { label: '失注', class: 'bg-emerald-50 text-emerald-700 border-emerald-100' };
    // 進行中判定（参画企業が1社以上）
    if (project.matched_bizs_count > 0) return { label: '進行中', class: 'bg-blue-50 text-blue-700 border-blue-100' };
    // デフォルト：準備中
    return { label: '準備中', class: 'bg-slate-50 text-slate-600 border-slate-100' };
};

function submit() {
    form.get(route('project.index'), {
        preserveState: true,
    });
}
</script>

<template>
    <Head title="案件一覧" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6">
            <div class="mb-6 flex flex-col md:flex-row gap-3 items-stretch md:items-end bg-card p-4 rounded-xl border shadow-sm">
                <div class="flex-1">
                    <label class="text-[10px] font-bold uppercase text-muted-foreground mb-1 block ml-1">Search</label>
                    <Input v-model="form.keyword" placeholder="案件名・顧客名..." class="h-10" />
                </div>
                <div class="flex gap-2">
                    <select v-model="form.status" class="flex-1 md:w-40 h-10 rounded-md border border-input bg-background px-3 text-sm">
                        <option value="">全ステータス</option>
                        <option value="0">準備中</option>
                        <option value="1">進行中</option>
                        <option value="2">失注</option>
                    </select>
                    <Button @click="submit" class="h-10 px-6 font-bold shadow-sm">
                        <Search class="w-4 h-4 md:mr-2" />
                        <span class="hidden md:inline">案件検索</span>
                    </Button>
                </div>
            </div>

            <div class="rounded-xl border bg-card shadow-md overflow-hidden">
                <table class="w-full text-sm hidden md:table">
                    <thead class="bg-muted/50 border-b">
                        <tr>
                            <th class="h-12 px-5 text-left font-bold text-muted-foreground">案件・発注機関</th>
                            <th class="h-12 px-4 text-left font-bold text-muted-foreground w-[220px]">スペック / 業種</th>
                            <th class="h-12 px-4 text-left font-bold text-muted-foreground w-[180px]">期限 / 参画状況</th>
                            <th class="h-12 px-4 text-right font-bold text-muted-foreground w-[160px]">想定金額</th>
                            <th class="h-12 px-4 text-right w-[60px]"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="project in projects?.data" :key="project.id" class="hover:bg-muted/10 transition-colors group">
                            <td class="p-5">
                                <div class="font-bold text-foreground leading-snug mb-2 text-[15px] group-hover:text-primary transition-colors">
                                    {{ project.title }}
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200 text-[10px] font-bold shadow-sm">
                                        <Building2 class="w-3 h-3" />
                                        {{ project.organization?.replace('機関名：', '') || '発注者不明' }}
                                    </div>
                                    <a v-if="project.url" :href="project.url" target="_blank" class="text-primary hover:text-primary/70 flex items-center gap-1 text-[10px] font-medium transition-colors">
                                        <Globe class="w-3 h-3" /> サイト
                                    </a>
                                </div>
                            </td>

                            <td class="p-4">
                                <div class="flex flex-col gap-1.5">
                                    <div v-if="project.bidding_qualifications" class="flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-100 text-[10px] font-bold w-fit">
                                        <ShieldCheck class="w-3 h-3" />
                                        <span class="truncate max-w-[150px]">{{ project.bidding_qualifications }}</span>
                                    </div>
                                    <div v-if="project.industry" class="flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-secondary/80 text-secondary-foreground border border-border text-[10px] font-medium w-fit">
                                        <Tag class="w-3 h-3 opacity-60" />
                                        <span class="truncate max-w-[150px]">{{ project.industry }}</span>
                                    </div>
                                </div>
                            </td>

                            <td class="p-4">
                                <div class="flex flex-col gap-2">
                                    <div class="flex items-center gap-2 font-bold text-orange-600 text-[11px]">
                                        <Clock class="w-3.5 h-3.5" />
                                        <span>入札: {{ formatDate(project.bid_date) }}</span>
                                    </div>
                                    <div v-if="project.matched_bizs_count > 0" class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-100 text-[10px] font-bold w-fit shadow-sm">
                                        <Users class="w-3 h-3" />
                                        参画 {{ project.matched_bizs_count }} 社
                                    </div>
                                    <div v-else class="text-[10px] text-muted-foreground/60 pl-1">
                                        参画企業なし
                                    </div>
                                </div>
                            </td>

                            <td class="p-4 text-right">
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-bold text-muted-foreground uppercase leading-none mb-1">Expected</span>
                                    <span class="font-mono font-bold text-base text-foreground tabular-nums">
                                        {{ formatCurrency(project.expected_amount) }}
                                    </span>
                                </div>
                            </td>

                            <td class="p-4 text-right">
                                <Button variant="ghost" size="icon" as-child class="rounded-full hover:bg-primary/10 hover:text-primary group-hover:translate-x-1 transition-all">
                                    <Link :href="`/project/edit/${project.id}`">
                                        <ArrowRight class="w-5 h-5" />
                                    </Link>
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="md:hidden divide-y">
                    <div v-for="project in projects?.data" :key="project.id" class="p-4 active:bg-muted/50 transition-colors">
                        <Link :href="`/project/edit/${project.id}`" class="block space-y-4">
                            <div class="flex justify-between items-start">
                                <span class="text-[10px] font-mono text-muted-foreground bg-muted px-1.5 py-0.5 rounded">#{{ project.id }}</span>
                                <span :class="['px-2 py-0.5 rounded-full text-[10px] font-bold border', getStatusConfig(project).class]">
                                    {{ getStatusConfig(project).label }}
                                </span>
                            </div>

                            <div class="space-y-3">
                                <div class="font-bold text-base text-foreground leading-tight">{{ project.title }}</div>
                                
                                <div class="flex flex-wrap gap-1.5">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-[10px] font-bold">{{ project.organization?.replace('機関名：', '') }}</span>
                                    <span v-if="project.matched_bizs_count > 0" class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[10px] font-bold border border-indigo-100">参画 {{ project.matched_bizs_count }}社</span>
                                </div>

                                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-dashed">
                                    <div class="text-[10px] space-y-1">
                                        <div class="text-muted-foreground">入札日</div>
                                        <div class="font-bold flex items-center gap-1"><Clock class="w-3 h-3 text-orange-500" />{{ formatDate(project.bid_date) }}</div>
                                    </div>
                                    <div class="text-[10px] space-y-1 text-right">
                                        <div class="text-muted-foreground font-bold">想定金額</div>
                                        <div class="font-bold text-primary text-sm tabular-nums">{{ formatCurrency(project.expected_amount) }}</div>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <div v-if="!projects?.data?.length" class="p-12 text-center text-muted-foreground bg-muted/20">
                    <p>該当する案件が見つかりませんでした。</p>
                </div>

                <div v-if="projects?.links?.length > 3" class="px-5 py-4 border-t bg-muted/20 flex flex-wrap justify-center gap-1">
                    <template v-for="(link, k) in projects.links" :key="k">
                        <div 
                            v-if="link.url === null" 
                            class="px-3 py-2 text-[12px] text-muted-foreground bg-muted/50 rounded-md border cursor-not-allowed"
                            v-html="link.label"
                        />
                        <Link 
                            v-else 
                            :href="link.url" 
                            class="px-3 py-2 text-[12px] rounded-md border transition-all hover:bg-primary/10"
                            :class="{ 'bg-primary text-primary-foreground font-bold border-primary shadow-sm': link.active, 'bg-card text-foreground': !link.active }"
                            v-html="link.label"
                            preserve-scroll
                            preserve-state
                        />
                    </template>
                </div>
            </div>
        </div>
    </AppLayout>
</template>