<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from "@inertiajs/vue3";
import { 
    Building2, Briefcase, TrendingUp, MapPin, 
    Phone, ShieldCheck, BarChart3, Users, HardHat,
    ExternalLink, Calendar, Award, Info, Landmark
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
        <div class="flex flex-col lg:flex-row h-[calc(100vh-64px)] overflow-hidden bg-background">
            
            <aside class="w-full lg:w-[340px] bg-card p-6 overflow-y-auto border-r border-neutral-200 dark:border-neutral-800 order-2 lg:order-1 flex flex-col gap-8 shadow-sm">
                <div>
                    <Badge variant="outline" class="font-mono text-[10px] mb-3 px-2 py-0.5 border-neutral-200 text-neutral-500 bg-neutral-50 dark:bg-neutral-900 dark:border-neutral-800">
                        管理番号: #{{ biz.permit_id }}
                    </Badge>
                    <h1 class="text-2xl font-black tracking-tight text-foreground leading-tight mb-3">
                        {{ biz.company_name }}
                    </h1>
                    <div class="inline-flex items-center gap-2 px-2.5 py-1.5 rounded-lg bg-neutral-100 dark:bg-neutral-800 border border-neutral-200 dark:border-neutral-700">
                        <Users class="w-3.5 h-3.5 text-neutral-500" />
                        <span class="text-xs font-bold text-neutral-700 dark:text-neutral-300">代表者：{{ biz.representative_name }}</span>
                    </div>
                </div>

                <div class="space-y-8">
                    <section>
                        <h3 class="text-[11px] font-bold text-neutral-400 mb-4 flex items-center gap-2 uppercase tracking-widest">
                            所在地・連絡先 <Separator class="flex-1 opacity-50 dark:bg-neutral-800" />
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3 text-xs group">
                                <div class="w-8 h-8 rounded-lg bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center shrink-0 border border-neutral-200 dark:border-neutral-700">
                                    <MapPin class="w-4 h-4 text-foreground" />
                                </div>
                                <div class="space-y-1">
                                    <span class="text-[10px] text-neutral-400 font-bold">住所</span>
                                    <p class="leading-relaxed font-bold">〒{{ biz.zip_code }}<br>{{ biz.address }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 text-xs group">
                                <div class="w-8 h-8 rounded-lg bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center shrink-0 border border-neutral-200 dark:border-neutral-700">
                                    <Phone class="w-4 h-4 text-foreground" />
                                </div>
                                <div class="space-y-1">
                                    <span class="text-[10px] text-neutral-400 font-bold">電話番号</span>
                                    <p class="font-bold">{{ biz.phone_number }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section>
                        <h3 class="text-[11px] font-bold text-neutral-400 mb-4 flex items-center gap-2 uppercase tracking-widest">
                            財務・経営規模 <Separator class="flex-1 opacity-50 dark:bg-neutral-800" />
                        </h3>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="bg-neutral-50 dark:bg-neutral-900/50 p-3 rounded-xl border border-neutral-200 dark:border-neutral-800">
                                <label class="text-[10px] text-neutral-500 block font-bold mb-1 text-center">資本金</label>
                                <p class="text-[13px] font-black tracking-tight text-center">{{ formatMoney(biz.capital) }}</p>
                            </div>
                            <div class="bg-neutral-50 dark:bg-neutral-900/50 p-3 rounded-xl border border-neutral-200 dark:border-neutral-800">
                                <label class="text-[10px] text-neutral-500 block font-bold mb-1 text-center">完工高比率</label>
                                <p class="text-[13px] font-black tracking-tight text-emerald-600 dark:text-emerald-500 text-center">{{ biz.sales_ratio }}%</p>
                            </div>
                            <div class="bg-neutral-50 dark:bg-neutral-900/50 p-3 rounded-xl border border-neutral-200 dark:border-neutral-800">
                                <label class="text-[10px] text-neutral-500 block font-bold mb-1 text-center">自己資本比率</label>
                                <p class="text-[13px] font-black tracking-tight text-center">
                                    {{ biz.financials?.equity_ratio ? biz.financials.equity_ratio + '%' : '-' }}
                                </p>
                            </div>
                            <div class="bg-neutral-50 dark:bg-neutral-900/50 p-3 rounded-xl border border-neutral-200 dark:border-neutral-800">
                                <label class="text-[10px] text-neutral-500 block font-bold mb-1 text-center">営業利益</label>
                                <p :class="['text-[13px] font-black tracking-tight text-center', (biz.financials?.operating_income < 0) ? 'text-red-500' : '']">
                                    {{ formatMoney(biz.financials?.operating_income) }}
                                </p>
                            </div>
                            <div class="bg-neutral-50 dark:bg-neutral-900/50 p-2.5 rounded-xl border border-neutral-200 dark:border-neutral-800 col-span-2 flex justify-between items-center px-4">
                                <label class="text-[10px] text-neutral-500 font-bold flex items-center gap-1">
                                    <Landmark class="w-3 h-3" /> 自己資本
                                </label>
                                <p class="text-xs font-bold">{{ formatMoney(biz.financials?.net_assets) }}</p>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="mt-auto pt-6 border-t border-dashed border-neutral-200 dark:border-neutral-800">
                    <div class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-neutral-50 dark:bg-neutral-900/50 border border-neutral-200 dark:border-neutral-800">
                        <Calendar class="w-3.5 h-3.5 text-neutral-400" />
                        <span class="text-[10px] font-bold text-neutral-500">審査基準日：{{ biz.review_base_date }}</span>
                    </div>
                </div>
            </aside>

            <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-neutral-50/30 dark:bg-black order-1 lg:order-2">
                <div class="max-w-6xl mx-auto space-y-10">
                    
                    <section>
                        <div class="flex items-center justify-between mb-6">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <div class="p-2 bg-neutral-900 dark:bg-neutral-100 rounded-lg">
                                        <Briefcase class="w-4 h-4 text-white dark:text-black" />
                                    </div>
                                    <h2 class="text-xl font-black tracking-tight">参画案件実績</h2>
                                </div>
                            </div>
                            <div v-if="biz.matches?.length" class="text-[10px] font-bold text-neutral-500 bg-card border border-neutral-200 dark:border-neutral-800 px-3 py-1.5 rounded-full">
                                {{ biz.matches.length }}件
                            </div>
                        </div>
                        
                        <div class="grid gap-3">
                            <div v-for="match in biz.matches" :key="match.id" 
                                 class="group bg-card border border-neutral-200 dark:border-neutral-800 rounded-2xl p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 hover:border-neutral-400 dark:hover:border-neutral-600 transition-all">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-neutral-100 dark:bg-neutral-800 flex items-center justify-center shrink-0">
                                        <Building2 class="w-5 h-5 text-neutral-400 group-hover:text-foreground" />
                                    </div>
                                    <div class="space-y-1">
                                        <h4 class="font-bold text-sm leading-none">{{ match.project.title }}</h4>
                                        <div class="flex items-center gap-3 text-[11px] text-neutral-500 font-medium">
                                            <span class="flex items-center gap-1"><MapPin class="w-3 h-3" /> {{ match.project.organization }}</span>
                                            <span class="text-amber-600 dark:text-amber-500 font-bold">/ {{ match.role }}</span>
                                        </div>
                                    </div>
                                </div>
                                <Badge variant="secondary" class="text-[10px] font-bold bg-neutral-100 dark:bg-neutral-800">{{ match.status_label }}</Badge>
                            </div>

                            <div v-if="!biz.matches?.length" 
                                class="flex flex-col items-center justify-center py-12 bg-neutral-100/30 dark:bg-neutral-900/10 rounded-3xl border-2 border-dashed border-neutral-200 dark:border-neutral-800">
                                <p class="text-xs font-bold text-neutral-500 uppercase tracking-widest">No Records Found</p>
                            </div>
                        </div>
                    </section>

                    <div class="relative py-2">
                        <Separator class="dark:bg-neutral-800" />
                        <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-neutral-50/30 dark:bg-black px-4 text-[10px] font-black text-neutral-400 uppercase tracking-widest">
                            経営事項審査詳細
                        </span>
                    </div>

                    <section>
                        <div class="flex items-center gap-2 mb-8">
                            <div class="p-2 bg-foreground text-background rounded-lg">
                                <TrendingUp class="w-5 h-5" />
                            </div>
                            <h2 class="text-xl font-black tracking-tight">経審スコア詳細</h2>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="score in biz.scores" :key="score.id" 
                                 class="bg-card border border-neutral-200 dark:border-neutral-800 rounded-2xl p-5 hover:border-neutral-400 dark:hover:border-neutral-700 transition-all">
                                
                                <div class="flex justify-between items-start mb-6">
                                    <div class="flex items-center gap-3">
                                        <div class="bg-neutral-100 dark:bg-neutral-800 p-2.5 rounded-xl border border-neutral-200 dark:border-neutral-700">
                                            <HardHat class="w-5 h-5 text-neutral-500" />
                                        </div>
                                        <div>
                                            <h4 class="font-black text-base tracking-tight mb-1">{{ score.work_category }}</h4>
                                            <Badge variant="outline" class="text-[9px] font-bold h-4 border-neutral-200 dark:border-neutral-700">{{ score.permit_type }}</Badge>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[9px] text-neutral-400 font-black block uppercase mb-1">P評点</span>
                                        <span class="text-2xl font-black italic text-foreground tracking-tighter">{{ score.p_score }}</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-2 pt-5 border-t border-dashed border-neutral-200 dark:border-neutral-800 relative z-10">
                                    <div class="space-y-1">
                                        <span class="text-[10px] font-bold text-neutral-400 block">完工高(平均)</span>
                                        <p class="text-xs font-black">{{ formatMoney(score.avg_sales) }}</p>
                                    </div>
                                    <div class="space-y-1 border-x border-neutral-200 dark:border-neutral-800 px-3">
                                        <span class="text-[10px] font-bold text-neutral-400 block">一級技術者</span>
                                        <p class="text-xs font-black">{{ score.details?.一級技術者 || 0 }}名</p>
                                    </div>
                                    <div class="space-y-1 text-right">
                                        <span class="text-[10px] font-bold text-neutral-400 block">評点 Z</span>
                                        <p class="text-xs font-black">{{ score.details?.評点Z || '-' }}</p>
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