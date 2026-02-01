<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from "@inertiajs/vue3";
import { 
    Building2, Briefcase, TrendingUp, MapPin, 
    Phone, ShieldCheck, BarChart3, Users, HardHat
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';

const props = defineProps({
    biz: Object,
});

const breadcrumbs = [
    { title: '企業一覧', href: '/biz/index' },
    { title: '企業詳細', href: '#' },
];

const formatMoney = (val) => {
    if (!val) return '-';
    return (val / 10000).toLocaleString() + '万円';
};
</script>

<template>
    <Head :title="`企業詳細 - ${biz.company_name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col lg:flex-row h-[calc(100vh-64px)] overflow-hidden bg-background">
            
            <aside class="w-full lg:w-[320px] bg-card p-6 overflow-y-auto shadow-sm border-r order-2 lg:order-1">
                <div class="mb-6">
                    <Badge variant="outline" class="font-mono text-[10px] mb-2">ID: #{{ biz.permit_id }}</Badge>
                    <h1 class="text-xl font-black tracking-tight text-foreground leading-tight mb-2">
                        {{ biz.company_name }}
                    </h1>
                    <p class="text-xs text-muted-foreground leading-relaxed flex items-center gap-1">
                        <Users class="w-3 h-3" /> 代表：{{ biz.representative_name }}
                    </p>
                </div>

                <div class="space-y-6">
                    <section class="space-y-3 px-1">
                        <h3 class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground border-b pb-1">所在地・連絡先</h3>
                        <div class="space-y-2.5">
                            <div class="flex items-start gap-2 text-xs">
                                <MapPin class="w-3.5 h-3.5 text-primary mt-0.5 shrink-0" />
                                <span class="leading-snug">〒{{ biz.zip_code }}<br>{{ biz.address }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-xs">
                                <Phone class="w-3.5 h-3.5 text-primary shrink-0" />
                                <span>{{ biz.phone_number }}</span>
                            </div>
                        </div>
                    </section>

                    <section class="space-y-3 px-1">
                        <h3 class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground border-b pb-1">企業概要</h3>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="bg-muted/30 p-2 rounded-lg">
                                <label class="text-[9px] text-muted-foreground block uppercase">資本金</label>
                                <p class="text-xs font-bold">{{ formatMoney(biz.capital) }}</p>
                            </div>
                            <div class="bg-muted/30 p-2 rounded-lg">
                                <label class="text-[9px] text-muted-foreground block uppercase">完工高比率</label>
                                <p class="text-xs font-bold text-emerald-600">{{ biz.sales_ratio }}%</p>
                            </div>
                            <div class="bg-muted/30 p-2 rounded-lg">
                                <label class="text-[9px] text-muted-foreground block uppercase">市区町村コード</label>
                                <p class="text-xs font-mono font-bold">{{ biz.city_code }}</p>
                            </div>
                            <div v-if="biz.admin_section" class="bg-muted/30 p-2 rounded-lg">
                                <label class="text-[9px] text-muted-foreground block uppercase">行政庁記入欄</label>
                                <p class="text-[10px]">{{ biz.admin_section }}</p>
                            </div>
                        </div>
                    </section>

                    <div class="pt-4 border-t border-dashed">
                        <div class="text-[9px] text-muted-foreground bg-slate-100 dark:bg-slate-800 p-2 rounded italic text-center">
                            最終審査基準日: {{ biz.review_base_date }}
                        </div>
                    </div>
                </div>
            </aside>

            <main class="flex-1 overflow-y-auto p-4 md:p-6 bg-slate-50/30 dark:bg-slate-950/20 order-1 lg:order-2">
                <div class="max-w-5xl mx-auto">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-xl font-black flex items-center gap-2 text-foreground">
                                <TrendingUp class="w-6 h-6 text-primary" />
                                経営事項審査スコアカード
                            </h2>
                            <p class="text-xs text-muted-foreground mt-1 ml-8">全業種の評価点および技術者・財務詳細</p>
                        </div>
                        <Badge variant="outline" class="bg-background shadow-sm px-4 py-1">
                            受審工種数: {{ biz.scores?.length || 0 }}業種
                        </Badge>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-2 gap-3">
                        <div v-for="score in biz.scores" :key="score.id" 
                             class="bg-card border rounded-xl p-3 shadow-sm hover:border-primary/50 transition-colors relative overflow-hidden group">
                            
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex items-center gap-2">
                                    <div class="bg-primary/10 text-primary p-1.5 rounded-lg">
                                        <HardHat class="w-3.5 h-3.5" />
                                    </div>
                                    <span class="font-bold text-sm tracking-tight">{{ score.work_category }}</span>
                                    <Badge variant="secondary" class="text-[9px] px-1.5 h-4">{{ score.permit_type }}</Badge>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-muted-foreground font-mono block leading-none mb-1 text-[10px]">P-SCORE</span>
                                    <span class="text-xl font-black leading-none tracking-tighter text-primary">{{ score.p_score }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-x-3 gap-y-1.5 pt-2 border-t border-dashed">
                                <div>
                                    <span class="text-[9px] text-muted-foreground block">完工高(平均)</span>
                                    <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">{{ formatMoney(score.avg_sales) }}</span>
                                </div>
                                <div>
                                    <span class="text-[9px] text-muted-foreground block">一級技術者</span>
                                    <span class="text-[11px] font-bold flex items-center gap-1">
                                        <Users class="w-2.5 h-2.5 opacity-50" /> {{ score.details?.一級技術者 || 0 }}人
                                    </span>
                                </div>
                                <div>
                                    <span class="text-[9px] text-muted-foreground block">評点 Z</span>
                                    <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">{{ score.details?.評点Z || '-' }}</span>
                                </div>
                                
                                <div class="col-span-3 flex gap-4 mt-0.5 opacity-60">
                                    <span class="text-[9px]">二級: {{ score.details?.二級技術者 || 0 }}人</span>
                                    <span class="text-[9px]">基幹: {{ score.details?.基幹技能者 || 0 }}人</span>
                                    <span class="text-[9px]">元請完工: {{ formatMoney(score.details?.元請完工高) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-12 mb-8">
                        <h2 class="text-lg font-black flex items-center gap-2 text-foreground mb-4">
                            <Briefcase class="w-5 h-5 text-primary" />
                            参画案件実績
                        </h2>
                        <div class="grid gap-3">
                            <div v-for="match in biz.matches" :key="match.id" 
                                 class="bg-card border rounded-xl p-4 flex justify-between items-center shadow-sm">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center">
                                        <Building2 class="w-5 h-5 text-slate-400" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-sm">{{ match.project.title }}</h4>
                                        <p class="text-[11px] text-muted-foreground">{{ match.project.organization }} | {{ match.role }}</p>
                                    </div>
                                </div>
                                <Badge variant="outline" class="text-[10px]">{{ match.status_label }}</Badge>
                            </div>
                            <div v-if="!biz.matches?.length" class="text-center py-10 bg-muted/20 rounded-xl border-2 border-dashed">
                                <p class="text-xs text-muted-foreground">実績データはまだありません</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </AppLayout>
</template>