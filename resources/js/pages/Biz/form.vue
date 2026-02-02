<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from "@inertiajs/vue3";
import { 
    Building2, Briefcase, TrendingUp, MapPin, 
    Phone, ShieldCheck, BarChart3, Users, HardHat,
    ExternalLink, Calendar, Award, Info
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
    if (!val) return '-';
    return (Number(val) / 10000).toLocaleString() + '万円';
};
</script>

<template>
    <Head :title="`企業詳細 - ${biz.company_name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col lg:flex-row h-[calc(100vh-64px)] overflow-hidden bg-background">
            
            <aside class="w-full lg:w-[340px] bg-card p-6 overflow-y-auto border-r order-2 lg:order-1 flex flex-col gap-8 shadow-sm">
                <div>
                    <Badge variant="outline" class="font-mono text-[10px] mb-3 px-2 py-0.5 border-slate-200 text-slate-500 bg-slate-50">
                        管理番号: #{{ biz.permit_id }}
                    </Badge>
                    <h1 class="text-2xl font-black tracking-tight text-foreground leading-tight mb-3">
                        {{ biz.company_name }}
                    </h1>
                    <div class="inline-flex items-center gap-2 px-2.5 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 border border-border/50">
                        <Users class="w-3.5 h-3.5 text-slate-500" />
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300">代表者：{{ biz.representative_name }}</span>
                    </div>
                </div>

                <div class="space-y-8">
                    <section>
                        <h3 class="text-[11px] font-bold text-slate-400 mb-4 flex items-center gap-2">
                            所在地・連絡先 <Separator class="flex-1 opacity-50" />
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3 text-xs group">
                                <div class="w-8 h-8 rounded-lg bg-primary/5 flex items-center justify-center shrink-0 border border-primary/10">
                                    <MapPin class="w-4 h-4 text-primary" />
                                </div>
                                <div class="space-y-1">
                                    <span class="text-[10px] text-slate-400 font-bold">住所</span>
                                    <p class="leading-relaxed font-bold">〒{{ biz.zip_code }}<br>{{ biz.address }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 text-xs group">
                                <div class="w-8 h-8 rounded-lg bg-primary/5 flex items-center justify-center shrink-0 border border-primary/10">
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
                            経営規模 <Separator class="flex-1 opacity-50" />
                        </h3>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-slate-50 dark:bg-slate-900 p-3 rounded-xl border border-border">
                                <label class="text-[10px] text-slate-500 block font-bold mb-1 text-center">資本金</label>
                                <p class="text-[13px] font-black tracking-tight text-center">{{ formatMoney(biz.capital) }}</p>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-900 p-3 rounded-xl border border-border">
                                <label class="text-[10px] text-slate-500 block font-bold mb-1 text-center">完工高比率</label>
                                <p class="text-[13px] font-black tracking-tight text-emerald-600 text-center">{{ biz.sales_ratio }}%</p>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-900 p-2.5 rounded-xl border border-border col-span-2 flex justify-between items-center px-4">
                                <label class="text-[10px] text-slate-500 font-bold">市区町村コード</label>
                                <p class="text-xs font-mono font-bold">{{ biz.city_code }}</p>
                            </div>
                        </div>
                    </section>

                    <section v-if="biz.admin_section">
                         <div class="bg-amber-50 border border-amber-200 p-3 rounded-xl">
                            <div class="flex items-center gap-1.5 mb-1.5 text-amber-700">
                                <Info class="w-3.5 h-3.5" />
                                <label class="text-[10px] font-black">行政庁記入欄</label>
                            </div>
                            <p class="text-[11px] leading-relaxed text-amber-800 font-medium">{{ biz.admin_section }}</p>
                        </div>
                    </section>
                </div>

                <div class="mt-auto pt-6 border-t border-dashed">
                    <div class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-border">
                        <Calendar class="w-3.5 h-3.5 text-slate-400" />
                        <span class="text-[10px] font-bold text-slate-500">審査基準日：{{ biz.review_base_date }}</span>
                    </div>
                </div>
            </aside>

            <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-slate-50/50 dark:bg-slate-950/50 order-1 lg:order-2">
                <div class="max-w-6xl mx-auto space-y-10">
                    
                    <section>
                        <div class="flex items-center justify-between mb-6">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <div class="p-2 bg-slate-800 dark:bg-slate-200 rounded-lg shadow-md">
                                        <Briefcase class="w-4 h-4 text-white dark:text-slate-900" />
                                    </div>
                                    <h2 class="text-xl font-black tracking-tight">参画案件実績</h2>
                                </div>
                                <p class="text-xs text-slate-500 font-medium ml-10">直近の施工・プロジェクト参画実績</p>
                            </div>
                            <div v-if="biz.matches?.length" class="text-[10px] font-bold text-slate-500 bg-white dark:bg-slate-900 px-4 py-2 rounded-full border shadow-sm">
                                実績総数：{{ biz.matches.length }}件
                            </div>
                        </div>
                        
                        <div class="grid gap-3">
                            <div v-for="match in biz.matches" :key="match.id" 
                                 class="group bg-card border rounded-2xl p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 shadow-sm hover:border-primary/40 transition-all duration-200">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center shrink-0">
                                        <Building2 class="w-6 h-6 text-slate-400 group-hover:text-primary transition-colors" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <h4 class="font-bold text-sm group-hover:text-primary transition-colors leading-none">{{ match.project.title }}</h4>
                                        <div class="flex flex-wrap items-center gap-y-2 gap-x-4 text-[11px] text-slate-500 font-medium">
                                            <span class="flex items-center gap-1"><MapPin class="w-3 h-3 opacity-70" /> {{ match.project.organization }}</span>
                                            <span class="flex items-center gap-1 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 px-1.5 py-0.5 rounded border border-amber-100 dark:border-amber-900/30">
                                                <Award class="w-3 h-3" /> {{ match.role }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                                    <Badge variant="secondary" class="text-[10px] px-3 font-bold py-1">
                                        {{ match.status_label }}
                                    </Badge>
                                    <button class="p-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                        <ExternalLink class="w-4 h-4 text-slate-400" />
                                    </button>
                                </div>
                            </div>

                            <div v-if="!biz.matches?.length" class="flex flex-col items-center justify-center py-16 bg-white/50 rounded-3xl border-2 border-dashed border-slate-200">
                                <Briefcase class="w-8 h-8 text-slate-200 mb-3" />
                                <p class="text-xs font-bold text-slate-400">登録されている実績はありません</p>
                            </div>
                        </div>
                    </section>

                    <div class="relative py-2">
                        <Separator class="opacity-50" />
                        <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-slate-50 dark:bg-slate-950 px-4 text-[10px] font-black text-slate-300 uppercase tracking-widest">
                            経営事項審査データ
                        </span>
                    </div>

                    <section>
                        <div class="flex items-center justify-between mb-8">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <div class="p-2 bg-primary rounded-lg shadow-lg shadow-primary/20">
                                        <TrendingUp class="w-5 h-5 text-primary-foreground" />
                                    </div>
                                    <h2 class="text-xl font-black tracking-tight">経審スコア詳細</h2>
                                </div>
                                <p class="text-[11px] text-slate-500 font-bold ml-11 uppercase opacity-70">業種別の評価点（P評点）および技術力詳細</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="score in biz.scores" :key="score.id" 
                                 class="bg-card border rounded-2xl p-5 shadow-sm hover:shadow-md hover:border-primary/30 transition-all duration-300 relative group overflow-hidden">
                                
                                <div class="flex justify-between items-start mb-6 relative z-10">
                                    <div class="flex items-center gap-3">
                                        <div class="bg-primary/5 text-primary p-2.5 rounded-xl border border-primary/10 transition-transform duration-300">
                                            <HardHat class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <h4 class="font-black text-base tracking-tight mb-1.5 group-hover:text-primary transition-colors">{{ score.work_category }}</h4>
                                            <div class="flex items-center gap-2">
                                                <Badge variant="outline" class="text-[9px] px-2 h-4 font-bold bg-slate-50 border-slate-200">{{ score.permit_type }}</Badge>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right bg-slate-50 dark:bg-slate-900 px-4 py-2 rounded-2xl border border-border shadow-inner">
                                        <span class="text-[9px] text-slate-400 font-black block leading-none mb-1 text-center uppercase">総合評点P</span>
                                        <span class="text-2xl font-black leading-none tracking-tighter text-primary italic">{{ score.p_score }}</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-2 pt-5 border-t border-dashed border-border relative z-10">
                                    <div class="space-y-1">
                                        <span class="text-[10px] font-bold text-slate-400 block">完工高(平均)</span>
                                        <p class="text-xs font-black">{{ formatMoney(score.avg_sales) }}</p>
                                    </div>
                                    <div class="space-y-1 border-x border-slate-100 dark:border-slate-800 px-3">
                                        <span class="text-[10px] font-bold text-slate-400 block">一級技術者</span>
                                        <p class="text-xs font-black flex items-center gap-1">
                                            <Award class="w-3.5 h-3.5 text-amber-500" /> {{ score.details?.一級技術者 || 0 }}<span class="text-[10px] font-medium opacity-50 ml-0.5">名</span>
                                        </p>
                                    </div>
                                    <div class="space-y-1 text-right">
                                        <span class="text-[10px] font-bold text-slate-400 block text-right">評点 Z</span>
                                        <p class="text-xs font-black text-primary/80">{{ score.details?.評点Z || '-' }}</p>
                                    </div>
                                </div>

                                <div class="mt-5 pt-3 flex items-center gap-4 border-t border-slate-100 dark:border-slate-800 opacity-60 group-hover:opacity-100 transition-opacity">
                                    <div class="flex items-center gap-1.5 text-[10px] font-bold">
                                        <span class="text-slate-400">二級：</span> {{ score.details?.二級技術者 || 0 }}名
                                    </div>
                                    <div class="flex items-center gap-1.5 text-[10px] font-bold">
                                        <span class="text-slate-400">基幹：</span> {{ score.details?.基幹技能者 || 0 }}名
                                    </div>
                                    <div class="ml-auto text-[10px] font-bold flex items-center gap-1.5">
                                        <span class="text-slate-400">元請完工：</span>
                                        <span class="text-slate-700 dark:text-slate-300 font-black">{{ formatMoney(score.details?.元請完工高) }}</span>
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