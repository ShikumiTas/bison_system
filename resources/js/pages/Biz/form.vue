<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from "@inertiajs/vue3";
import {
    Building2, Briefcase, TrendingUp, MapPin,
    Phone, ShieldCheck, BarChart3, Users, HardHat,
    ExternalLink, Calendar, Award, Info, PieChart, Landmark
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';

const props = defineProps({
    biz: Object,
});

const breadcrumbs = [
    { title: '企業一覧', href: '/biz/index' },
    { title: '企業詳細', href: '#' },
];

const formatMoney = (val) => {
    if (!val && val !== 0) return '-';
    const prefix = val < 0 ? '▲' : '';
    return prefix + (Math.abs(Number(val)) / 10000).toLocaleString() + '万円';
};
</script>

<template>

    <Head :title="`企業詳細 - ${biz.company_name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex flex-col lg:flex-row h-full lg:h-[calc(100vh-64px)] overflow-y-auto lg:overflow-hidden bg-background">

            <aside
                class="w-full lg:w-[360px] bg-card p-5 md:p-6 border-b lg:border-r order-1 lg:order-1 flex flex-col gap-6 md:gap-8 shadow-sm">
                <div>
                    <Badge variant="outline"
                        class="font-mono text-[10px] mb-3 px-2 py-0.5 border-slate-200 text-slate-500 bg-slate-50">
                        管理番号: #{{ biz.permit_id }}
                    </Badge>
                    <h1 class="text-xl md:text-2xl font-black tracking-tight text-foreground leading-tight mb-3">
                        {{ biz.company_name }}
                    </h1>
                    <div
                        class="inline-flex items-center gap-2 px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 border border-border/50">
                        <Users class="w-3.5 h-3.5 text-slate-500" />
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">代表者：{{
                            biz.representative_name }}</span>
                    </div>
                </div>

                <div class="space-y-6 md:space-y-8">
                    <section>
                        <h3 class="text-[11px] font-bold text-slate-400 mb-4 flex items-center gap-2">
                            所在地・連絡先
                            <Separator class="flex-1 opacity-50" />
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3 text-xs">
                                <div
                                    class="w-8 h-8 rounded-lg bg-primary/5 flex items-center justify-center shrink-0 border border-primary/10">
                                    <MapPin class="w-4 h-4 text-primary" />
                                </div>
                                <div class="space-y-1">
                                    <span class="text-[10px] text-slate-400 font-bold">住所</span>
                                    <p class="leading-relaxed font-bold">〒{{ biz.zip_code }}<br>{{ biz.address }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 text-xs">
                                <div
                                    class="w-8 h-8 rounded-lg bg-primary/5 flex items-center justify-center shrink-0 border border-primary/10">
                                    <Phone class="w-4 h-4 text-primary" />
                                </div>
                                <div class="space-y-1">
                                    <span class="text-[10px] text-slate-400 font-bold">電話番号</span>
                                    <p class="font-bold">{{ biz.phone_number }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section>
                        <h3 class="text-[11px] font-bold text-slate-400 mb-4 flex items-center gap-2">
                            財務・経営規模
                            <Separator class="flex-1 opacity-50" />
                        </h3>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-slate-50 dark:bg-slate-900 p-2.5 md:p-3 rounded-xl border border-border">
                                <label
                                    class="text-[9px] md:text-[10px] text-slate-500 block font-bold mb-1 text-center">資本金</label>
                                <p class="text-[12px] md:text-[13px] font-black tracking-tight text-center">{{
                                    formatMoney(biz.capital) }}</p>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-900 p-2.5 md:p-3 rounded-xl border border-border">
                                <label
                                    class="text-[9px] md:text-[10px] text-slate-500 block font-bold mb-1 text-center">完工高比率</label>
                                <p
                                    class="text-[12px] md:text-[13px] font-black tracking-tight text-emerald-600 text-center">
                                    {{ biz.sales_ratio }}%</p>
                            </div>

                            <div
                                class="bg-indigo-50/50 dark:bg-indigo-900/20 p-2.5 md:p-3 rounded-xl border border-indigo-100 dark:border-indigo-900/30">
                                <label
                                    class="text-[9px] md:text-[10px] text-indigo-600 dark:text-indigo-400 block font-bold mb-1 text-center">自己資本比率</label>
                                <p
                                    class="text-[12px] md:text-[13px] font-black tracking-tight text-indigo-700 dark:text-indigo-300 text-center">
                                    {{ biz.financials?.equity_ratio ? biz.financials.equity_ratio + '%' : '-' }}
                                </p>
                            </div>

                            <div
                                class="bg-blue-50/50 dark:bg-blue-900/20 p-2.5 md:p-3 rounded-xl border border-blue-100 dark:border-blue-900/30">
                                <label
                                    class="text-[9px] md:text-[10px] text-blue-600 dark:text-blue-400 block font-bold mb-1 text-center">営業利益</label>
                                <p
                                    :class="['text-[12px] md:text-[13px] font-black tracking-tight text-center', (biz.financials?.operating_income < 0) ? 'text-red-500' : 'text-blue-700 dark:text-blue-300']">
                                    {{ formatMoney(biz.financials?.operating_income) }}
                                </p>
                            </div>

                            <div
                                class="bg-slate-50 dark:bg-slate-900 p-2.5 rounded-xl border border-border col-span-2 flex justify-between items-center px-4">
                                <label class="text-[10px] text-slate-500 font-bold flex items-center gap-1">
                                    <Landmark class="w-3 h-3" /> 自己資本(純資産)
                                </label>
                                <p class="text-xs font-bold">{{ formatMoney(biz.financials?.net_assets) }}</p>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="mt-auto pt-6 border-t border-dashed hidden lg:block">
                    <div
                        class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-border">
                        <Calendar class="w-3.5 h-3.5 text-slate-400" />
                        <span class="text-[10px] font-bold text-slate-500">審査基準日：{{ biz.review_base_date }}</span>
                    </div>
                </div>
            </aside>

            <main class="flex-1 lg:overflow-y-auto p-4 md:p-8 bg-slate-50/50 dark:bg-slate-950/50 order-2 lg:order-2">
                <div class="max-w-6xl mx-auto space-y-8 md:space-y-10">

                    <section>
                        <div class="flex items-center justify-between mb-6">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <div class="p-2 bg-slate-800 dark:bg-slate-200 rounded-lg">
                                        <Briefcase class="w-4 h-4 text-white dark:text-slate-900" />
                                    </div>
                                    <h2 class="text-lg md:text-xl font-black tracking-tight">参画案件実績</h2>
                                </div>
                                <p class="text-[10px] md:text-xs text-slate-500 font-medium ml-10">直近の施工・プロジェクト参画実績</p>
                            </div>
                        </div>

                        <div class="grid gap-3">
                            <div v-for="match in biz.matches" :key="match.id"
                                class="group bg-card border rounded-2xl p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 shadow-sm hover:border-primary/40 transition-all">
                                <div class="flex items-start md:items-center gap-3 md:gap-4">
                                    <div
                                        class="w-10 h-10 md:w-12 md:h-12 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0">
                                        <Building2
                                            class="w-5 h-5 md:w-6 md:h-6 text-slate-400 group-hover:text-primary transition-colors" />
                                    </div>
                                    <div class="space-y-1">
                                        <h4 class="font-bold text-sm leading-tight">{{ match.project.title }}</h4>
                                        <div
                                            class="flex flex-wrap items-center gap-y-1.5 gap-x-3 text-[10px] md:text-[11px] text-slate-500 font-medium">
                                            <span class="flex items-center gap-1">
                                                <MapPin class="w-3 h-3 opacity-70" /> {{ match.project.organization }}
                                            </span>
                                            <span
                                                class="flex items-center gap-1 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 px-1.5 py-0.5 rounded border border-amber-100 dark:border-amber-900/30">
                                                <Award class="w-3 h-3" /> {{ match.role }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-between w-full sm:w-auto sm:justify-end gap-3 border-t sm:border-0 pt-3 sm:pt-0">
                                    <Badge variant="secondary" class="text-[10px] px-3 font-bold py-1">
                                        {{ match.status_label }}
                                    </Badge>
                                    <button
                                        class="p-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                        <ExternalLink class="w-4 h-4 text-slate-400" />
                                    </button>
                                </div>
                            </div>

                            <div v-if="!biz.matches?.length"
                                class="flex flex-col items-center justify-center py-12 bg-slate-950/[0.03] dark:bg-black/40 rounded-3xl border-2 border-dashed border-slate-200">
                                <Briefcase class="w-8 h-8 text-slate-300 mb-3" />
                                <p class="text-xs font-bold text-slate-400">登録されている実績はありません</p>
                            </div>
                        </div>
                    </section>

                    <div class="relative py-2">
                        <Separator class="opacity-50" />
                        <span
                            class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-slate-50 dark:bg-slate-950 px-4 text-[9px] md:text-[10px] font-black text-slate-300 uppercase tracking-widest">
                            経営事項審査データ
                        </span>
                    </div>

                    <section>
                        <div class="flex items-center justify-between mb-6 md:mb-8">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <div class="p-2 bg-primary rounded-lg shadow-lg shadow-primary/20">
                                        <TrendingUp class="w-4 h-4 md:w-5 md:h-5 text-primary-foreground" />
                                    </div>
                                    <h2 class="text-lg md:text-xl font-black tracking-tight">経審スコア詳細</h2>
                                </div>
                                <p
                                    class="text-[10px] md:text-[11px] text-slate-500 font-bold ml-10 uppercase opacity-70">
                                    業種別の評価点（P評点）</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="score in biz.scores" :key="score.id"
                                class="bg-card border rounded-2xl p-4 md:p-5 shadow-sm hover:shadow-md transition-all relative overflow-hidden">

                                <div class="flex justify-between items-start mb-6">
                                    <div class="flex items-center gap-3">
                                        <div class="bg-primary/5 text-primary p-2 rounded-xl border border-primary/10">
                                            <HardHat class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <h4 class="font-black text-sm md:text-base tracking-tight mb-1">{{
                                                score.work_category }}</h4>
                                            <Badge variant="outline" class="text-[9px] px-2 h-4 font-bold bg-slate-50">
                                                {{ score.permit_type }}</Badge>
                                        </div>
                                    </div>
                                    <div
                                        class="text-right bg-slate-50 dark:bg-slate-900 px-3 py-1.5 md:px-4 md:py-2 rounded-2xl border border-border">
                                        <span
                                            class="text-[8px] md:text-[9px] text-slate-400 font-black block leading-none mb-1 text-center">総合評点P</span>
                                        <span
                                            class="text-xl md:text-2xl font-black leading-none tracking-tighter text-primary italic">{{
                                            score.p_score }}</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-2 pt-4 border-t border-dashed border-border">
                                    <div class="space-y-1">
                                        <span
                                            class="text-[9px] md:text-[10px] font-bold text-slate-400 block text-center sm:text-left">完工高(平均)</span>
                                        <p class="text-[10px] md:text-xs font-black text-center sm:text-left">{{
                                            formatMoney(score.avg_sales) }}</p>
                                    </div>
                                    <div
                                        class="space-y-1 border-x border-slate-100 dark:border-slate-800 px-2 md:px-3 text-center">
                                        <span
                                            class="text-[9px] md:text-[10px] font-bold text-slate-400 block">一級技術者</span>
                                        <p
                                            class="text-[10px] md:text-xs font-black flex items-center justify-center gap-1">
                                            <Award class="w-3 h-3 text-amber-500" /> {{ score.details?.一級技術者 || 0 }}名
                                        </p>
                                    </div>
                                    <div class="space-y-1 text-right">
                                        <span class="text-[9px] md:text-[10px] font-bold text-slate-400 block">評点
                                            Z</span>
                                        <p class="text-[10px] md:text-xs font-black text-primary/80">{{
                                            score.details?.評点Z || '-' }}</p>
                                    </div>
                                </div>

                                <div
                                    class="mt-4 pt-3 flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-slate-50 text-[9px] md:text-[10px] font-bold opacity-70">
                                    <span class="text-slate-400">二級: <span class="text-foreground">{{
                                            score.details?.二級技術者 || 0 }}名</span></span>
                                    <span class="text-slate-400">基幹: <span class="text-foreground">{{
                                            score.details?.基幹技能者 || 0 }}名</span></span>
                                    <div class="sm:ml-auto text-slate-400">
                                        元請完工: <span class="text-foreground font-black">{{
                                            formatMoney(score.details?.元請完工高) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </AppLayout>
</template>