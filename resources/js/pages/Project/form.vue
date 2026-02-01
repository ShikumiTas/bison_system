<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import BizSearchInput from '@/pages/Biz/Partials/BizSearchInput.vue';
import { Head, Link, router } from "@inertiajs/vue3";
import { 
    Building2, MoreVertical, Clock, ExternalLink, 
    Calendar, MapPin, Truck, Globe, Edit, ShieldCheck, Tag
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    DropdownMenu, 
    DropdownMenuContent, 
    DropdownMenuItem, 
    DropdownMenuTrigger 
} from '@/components/ui/dropdown-menu';

const props = defineProps({
    project: Object,
    relatedBizs: Array,
});

// ISO形式を yyyy/mm/dd に整形（時刻部分はカット）
const formatDate = (dateStr: string | null) => {
    if (!dateStr) return '未設定';
    return dateStr.split('T')[0].replaceAll('-', '/');
};

const removeBiz = (bizId: number) => {
    if (!confirm('この企業の紐付けを解除しますか？')) return;
    
    router.delete(`/project/${props.project.id}/matching/${bizId}`, {
        preserveScroll: true,
    });
};

const breadcrumbs = [
    { title: '案件一覧', href: '/project/index' },
    { title: '案件詳細', href: '#' },
];
</script>

<template>
    <Head :title="`案件詳細 - ${project.title}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col lg:flex-row h-[calc(100vh-64px)] overflow-hidden bg-background">
            
            <aside class="w-full lg:w-[380px] border-r bg-card p-6 overflow-y-auto shadow-sm transition-colors">
                <div class="mb-6">
                    <div class="flex flex-wrap gap-2 mb-2">
                        <Badge variant="outline" class="border-primary/20 text-primary font-mono text-[10px]">
                            ID: #{{ project.project_external_id || project.id }}
                        </Badge>
                        <Badge v-if="project.bidding_type" variant="secondary" class="text-[10px]">
                            {{ project.bidding_type }}
                        </Badge>
                    </div>
                    <h1 class="text-xl font-black tracking-tight text-foreground leading-tight">
                        {{ project.title }}
                    </h1>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground block mb-2">発注機関 / 所在地</label>
                        <div class="space-y-2 pl-1">
                            <p class="font-bold text-foreground/90 flex items-center gap-2 text-sm">
                                <Building2 class="w-4 h-4 text-primary/60" /> {{ project.organization || '不明' }}
                            </p>
                            <p class="text-xs text-muted-foreground flex items-start gap-2 leading-relaxed">
                                <MapPin class="w-4 h-4 shrink-0 opacity-50" /> {{ project.organization_address || '住所情報なし' }}
                            </p>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-emerald-500/5 border border-emerald-500/10">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-emerald-600 block mb-1.5">履行/納品場所</label>
                        <p class="text-xs font-bold text-emerald-900 dark:text-emerald-300 flex items-start gap-2">
                            <Truck class="w-4 h-4 shrink-0 text-emerald-500" /> {{ project.delivery_location || '仕様書による' }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 rounded-lg bg-blue-500/5 border border-blue-500/10">
                            <label class="text-[10px] font-bold uppercase tracking-widest text-blue-600 block mb-1">公示日</label>
                            <p class="text-xs font-bold text-blue-800 dark:text-blue-300 flex items-center gap-2">
                                <Calendar class="w-3.5 h-3.5 opacity-60" /> {{ formatDate(project.notice_date) }}
                            </p>
                        </div>
                        <div class="p-3 rounded-lg bg-orange-500/5 border border-orange-500/10">
                            <label class="text-[10px] font-bold uppercase tracking-widest text-orange-600 block mb-1">入札日</label>
                            <p class="text-xs font-bold text-orange-800 dark:text-orange-300 flex items-center gap-2">
                                <Clock class="w-3.5 h-3.5 opacity-60" /> {{ formatDate(project.bid_date) }}
                            </p>
                        </div>
                    </div>

                    <div v-if="project.url" class="pt-2">
                        <Button variant="outline" as-child class="w-full justify-between text-[11px] h-9 text-primary border-primary/10 hover:bg-primary/5">
                            <a :href="project.url" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2">
                                <Globe class="w-4 h-4" /> 案件詳細（外部） <ExternalLink class="w-3 h-3 opacity-40" />
                            </a>
                        </Button>
                    </div>

                    <div class="pt-4 border-t border-dashed border-border/60 space-y-4">
                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground flex items-center gap-2 mb-1">
                                <ShieldCheck class="w-3.5 h-3.5 text-slate-400" /> 入札資格
                            </label>
                            <p class="text-xs font-bold text-foreground pl-5.5">
                                {{ project.bidding_qualifications || '未設定' }}
                            </p>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground flex items-center gap-2 mb-1">
                                <Tag class="w-3.5 h-3.5 text-slate-400" /> 業種カテゴリ
                            </label>
                            <p class="text-xs font-bold text-foreground pl-5.5">
                                {{ project.industry || '未設定' }}
                            </p>
                        </div>
                    </div>

                </div>
            </aside>

            <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-slate-50/30 dark:bg-slate-950/20">
                <div class="max-w-4xl mx-auto">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold flex items-center gap-2 text-foreground">
                            <Building2 class="w-5 h-5 text-primary" />
                            協力会社・関連企業
                        </h2>
                        <BizSearchInput 
                            :projectId="project.id" 
                            :relatedBizs="relatedBizs"
                            :projectAddress="project.organization_address" 
                        />
                    </div>

                    <div class="grid gap-4">
                        <div v-for="biz in relatedBizs" :key="biz.id" 
                            class="group bg-card text-card-foreground rounded-xl border border-border p-4 shadow-sm hover:shadow-md transition-all border-l-4"
                            :class="biz.status === 'completed' ? 'border-l-emerald-500' : 'border-l-orange-400'">
                            
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="font-bold text-foreground">
                                            <a :href="`/biz/edit/${biz.id}`" target="_blank" rel="noopener noreferrer" class="font-bold text-foreground hover:text-primary hover:underline transition-colors underline-offset-4 decoration-primary/30 flex items-center gap-1 group/link">
                                                {{ biz.company_name }}
                                                <ExternalLink class="w-3 h-3 opacity-0 group-hover/link:opacity-100 transition-opacity" />
                                            </a>
                                        </h3>
                                        <span class="text-[10px] bg-secondary text-secondary-foreground px-1.5 py-0.5 rounded font-mono font-bold">
                                            P:{{ biz.p_score }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-muted-foreground mb-3 font-medium">担当役割: {{ biz.role || '未設定' }}</p>
                                    
                                    <div class="flex items-center gap-4">
                                        <select class="text-xs border-none bg-secondary text-secondary-foreground rounded px-2 py-1 focus:ring-1 ring-primary cursor-pointer font-bold">
                                            <option>見積依頼中</option>
                                            <option selected>契約済み</option>
                                            <option>施工完了</option>
                                        </select>
                                        <div class="flex items-center gap-1 text-[11px] text-muted-foreground">
                                            <Clock class="w-3 h-3" />
                                            最終更新: 2時間前
                                        </div>
                                    </div>
                                </div>
                                
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="opacity-0 group-hover:opacity-100 transition-opacity hover:bg-accent">
                                            <MoreVertical class="w-4 h-4 text-muted-foreground" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-40">
                                        <DropdownMenuItem @click="removeBiz(biz.id)" class="text-destructive focus:text-destructive cursor-pointer">
                                            紐付けを解除
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </div>
                        <div v-if="relatedBizs.length === 0" class="border-2 border-dashed border-border rounded-xl p-12 text-center text-muted-foreground bg-card/50">
                            <div class="inline-flex p-3 rounded-full bg-secondary mb-4">
                                <Building2 class="w-6 h-6 opacity-20" />
                            </div>
                            <p class="text-sm font-medium">まだ企業が紐付けられていません。</p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </AppLayout>
</template>