<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import BizSearchInput from '@/pages/Biz/Partials/BizSearchInput.vue';
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { 
    Building2, MoreVertical, Clock, ExternalLink, 
    Calendar, MapPin, Truck, Globe, ShieldCheck, 
    CheckCircle2, StickyNote, Phone, Edit3, Save, X, DollarSign
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
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

const isEditing = ref(false);

const form = useForm({
    expected_amount: props.project.expected_amount || '',
    status: props.project.status ?? 0, // 案件自体のステータス（nullを考慮し ?? を使用）
    status_memo: props.project.status_memo || '',
});

const saveProject = () => {
    form.patch(`/project/${props.project.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};

// ステータスのラベルと色の定義（表示用：数値で統一）
const getProjectStatus = (project: any) => {
    const statusMap: Record<number, { text: string; class: string }> = {
        0: { text: '検討中', class: 'bg-slate-500/10 text-slate-600 border-slate-200' },
        1: { text: '調査・準備', class: 'bg-blue-500/10 text-blue-600 border-blue-200' },
        2: { text: '応札済', class: 'bg-indigo-500/10 text-indigo-700 border-indigo-200' },
        3: { text: '落札', class: 'bg-emerald-500/10 text-emerald-700 border-emerald-200' },
        4: { text: '辞退・失注', class: 'bg-rose-500/10 text-rose-700 border-rose-200' },
        5: { text: '完了', class: 'bg-amber-500/10 text-amber-700 border-amber-200' },
    };

    // 1. DBの status カラムを優先参照
    if (project.status !== undefined && project.status !== null && statusMap[project.status]) {
        return statusMap[project.status];
    }

    // 2. statusが0または未定義の場合、参画企業がいれば「調査・準備(1)」とみなすフォールバック
    if (props.relatedBizs && props.relatedBizs.length > 0) {
        return statusMap[1];
    }

    return statusMap[0];
};

const formatDate = (dateStr: string | null) => {
    if (!dateStr) return '未設定';
    return dateStr.split('T')[0].replaceAll('-', '/');
};

const formatCurrency = (value: any) => {
    if (!value) return '---';
    return new Intl.NumberFormat('ja-JP', { style: 'currency', currency: 'JPY' }).format(value);
};

const updateMatching = (bizId: number, data: object) => {
    router.patch(`/project/${props.project.id}/matching/${bizId}`, data, {
        preserveScroll: true,
        onSuccess: () => console.log('更新完了'),
        onError: () => alert('更新に失敗しました。')
    });
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
        <div class="flex flex-col lg:flex-row lg:h-[calc(100vh-64px)] overflow-hidden bg-background text-left">
            
            <aside class="w-full lg:w-[380px] border-r border-border bg-card p-4 md:p-6 overflow-y-auto shadow-sm relative">
                <div class="absolute top-4 right-4 z-10">
                    <Button variant="ghost" size="sm" @click="isEditing = !isEditing" class="h-8 w-8 p-0 rounded-full">
                        <X v-if="isEditing" class="w-4 h-4 text-muted-foreground" />
                        <Edit3 v-else class="w-4 h-4 text-muted-foreground" />
                    </Button>
                </div>

                <div class="mb-6">
                    <div class="flex flex-wrap gap-2 mb-3">
                        <Badge variant="outline" class="border-primary/20 text-primary font-mono text-[10px] bg-primary/5">
                            ID: #{{ project.project_external_id || project.id }}
                        </Badge>
                        <Badge variant="outline" :class="[getProjectStatus(project).class, 'text-[10px] font-bold border']">
                            {{ getProjectStatus(project).text }}
                        </Badge>
                    </div>
                    <h1 class="text-xl font-black tracking-tight text-foreground leading-tight">
                        {{ project.title }}
                    </h1>
                </div>

                <div class="space-y-6">
                    <div v-if="isEditing" class="p-4 rounded-xl bg-primary/5 border border-primary/20 space-y-4 animate-in fade-in zoom-in-95 duration-200">
                        <div>
                            <label class="text-[10px] font-bold uppercase text-primary block mb-1">案件ステータス</label>
                            <select 
                                v-model="form.status"
                                class="w-full text-xs border border-input bg-background rounded-md px-2 py-1.5 font-bold cursor-pointer focus:ring-2 ring-primary/20 outline-none"
                            >
                                <option :value="0">検討中</option>
                                <option :value="1">調査・準備</option>
                                <option :value="2">応札済</option>
                                <option :value="3">落札</option>
                                <option :value="4">辞退・失注</option>
                                <option :value="5">完了</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold uppercase text-primary block mb-1">想定金額</label>
                            <div class="relative">
                                <DollarSign class="absolute left-2 top-2 w-3.5 h-3.5 text-muted-foreground" />
                                <Input type="number" v-model="form.expected_amount" class="h-8 pl-7 text-xs font-bold" />
                            </div>
                        </div>
                        <div>
                            <label class="text-[10px] font-bold uppercase text-primary block mb-1">状況メモ</label>
                            <div class="relative group/memo">
                                <textarea 
                                    v-model="form.status_memo" 
                                    placeholder="現在の進捗など..."
                                    class="w-full text-[11px] bg-background text-foreground border border-input rounded-md px-2 py-1.5 h-9 resize-none focus:h-32 transition-all duration-300 focus:ring-2 ring-primary/20 outline-none"
                                ></textarea>
                                <StickyNote class="absolute right-2 top-2 w-3 h-3 text-muted-foreground opacity-30 group-focus-within/memo:opacity-0" />
                            </div>
                        </div>
                        <Button @click="saveProject" size="sm" class="w-full h-8 font-bold text-[11px]" :disabled="form.processing">
                            <Save class="w-3.5 h-3.5 mr-1" /> 更新内容を保存
                        </Button>
                    </div>

                    <template v-else>
                        <div class="p-3 rounded-lg bg-blue-500/5 border border-blue-500/10 text-left">
                            <label class="text-[10px] font-bold uppercase tracking-widest text-blue-600 block mb-1">想定金額</label>
                            <p class="text-lg font-black text-foreground">{{ formatCurrency(project.expected_amount) }}</p>
                        </div>
                        <div v-if="project.status_memo" class="p-3 rounded-lg bg-amber-500/5 border border-amber-500/10 text-left">
                            <label class="text-[10px] font-bold uppercase tracking-widest text-amber-600 block mb-1">状況メモ</label>
                            <p class="text-[11px] text-muted-foreground whitespace-pre-wrap leading-relaxed">{{ project.status_memo }}</p>
                        </div>
                    </template>

                    <div class="text-left">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground block mb-2">発注機関 / 所在地</label>
                        <div class="space-y-2 pl-1">
                            <p class="font-bold text-foreground flex items-center gap-2 text-sm">
                                <Building2 class="w-4 h-4 text-primary/60" /> {{ project.organization || '不明' }}
                            </p>
                            <p class="text-xs text-muted-foreground flex items-start gap-2 leading-relaxed text-left">
                                <MapPin class="w-4 h-4 shrink-0 opacity-50" /> {{ project.organization_address || '住所情報なし' }}
                            </p>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-left">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-emerald-600 block mb-1.5">履行/納品場所</label>
                        <p class="text-xs font-bold text-foreground flex items-start gap-2">
                            <Truck class="w-4 h-4 shrink-0 text-emerald-500" /> {{ project.delivery_location || '仕様書による' }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 rounded-lg bg-muted border border-border text-left">
                            <label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground block mb-1">公示日</label>
                            <p class="text-xs font-bold text-foreground flex items-center gap-2 truncate">
                                <Calendar class="w-3.5 h-3.5 text-primary/60" /> {{ formatDate(project.notice_date) }}
                            </p>
                        </div>
                        <div class="p-3 rounded-lg bg-muted border border-border text-left">
                            <label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground block mb-1">入札日</label>
                            <p class="text-xs font-bold text-foreground flex items-center gap-2 truncate">
                                <Clock class="w-3.5 h-3.5 text-orange-500/60" /> {{ formatDate(project.bid_date) }}
                            </p>
                        </div>
                    </div>

                    <div v-if="project.url" class="pt-2">
                        <Button variant="outline" as-child class="w-full justify-between text-[11px] h-9 border-primary/20 hover:bg-primary/5 transition-colors">
                            <a :href="project.url" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2">
                                <Globe class="w-4 h-4 text-primary" /> 案件詳細（外部） <ExternalLink class="w-3 h-3 opacity-40" />
                            </a>
                        </Button>
                    </div>

                    <div class="pt-4 border-t border-dashed border-border text-left">
                        <label class="text-[10px] font-bold uppercase tracking-widest text-muted-foreground flex items-center gap-2 mb-1">
                            <ShieldCheck class="w-3.5 h-3.5 opacity-50" /> 入札資格
                        </label>
                        <p class="text-xs font-bold text-foreground pl-5.5 leading-relaxed">{{ project.bidding_qualifications || '未設定' }}</p>
                    </div>
                </div>
            </aside>

            <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-background/50">
                <div class="max-w-4xl mx-auto">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                        <h2 class="text-lg font-black flex items-center gap-2 text-foreground">
                            <CheckCircle2 class="w-5 h-5 text-primary" /> 検討・参画中の企業
                        </h2>
                        <BizSearchInput :projectId="project.id" :relatedBizs="relatedBizs" :projectAddress="project.organization_address" />
                    </div>

                    <div class="grid gap-4">
                        <div v-for="biz in relatedBizs" :key="biz.id" 
                            class="group bg-card text-card-foreground rounded-xl border border-border p-4 md:p-5 shadow-sm hover:shadow-md transition-all border-l-4 text-left"
                            :class="{
                                'border-l-blue-500': biz.status === 'ongoing',
                                'border-l-emerald-500': biz.status === 'completed',
                                'border-l-amber-400': biz.status === 'requesting' || biz.status === 'received',
                                'border-l-muted': !biz.status
                            }">
                            
                            <div class="flex items-start justify-between gap-2 md:gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <h3 class="text-sm md:text-base font-bold text-foreground flex items-center gap-1 group/link">
                                            <a :href="`/biz/edit/${biz.id}`" target="_blank" class="hover:text-primary hover:underline underline-offset-4 truncate max-w-[200px] md:max-w-none">
                                                {{ biz.company_name }}
                                            </a>
                                            <ExternalLink class="w-3.5 h-3.5 opacity-30 group-hover/link:opacity-100" />
                                        </h3>
                                        <div class="flex gap-1">
                                            <Badge variant="outline" class="bg-muted/50 text-[10px] font-black h-5">P:{{ biz.score_p || '-' }}</Badge>
                                            <Badge v-if="biz.rank" class="bg-foreground text-background text-[9px] h-5 italic font-black px-1.5">{{ biz.rank }}</Badge>
                                        </div>
                                    </div>

                                    <div class="flex flex-col md:flex-row md:flex-wrap gap-x-4 gap-y-1 mb-4 text-[11px] text-muted-foreground">
                                        <div class="flex items-center gap-1"><MapPin class="w-3 h-3 shrink-0" /> <span class="truncate">{{ biz.address || '住所未登録' }}</span></div>
                                        <div class="text-primary font-bold flex items-center gap-1"><Phone class="w-3 h-3 shrink-0" /> {{ biz.phone_number || '番号未登録' }}</div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 bg-muted/50 p-3 rounded-lg border border-border/50">
                                        <div>
                                            <label class="text-[9px] font-bold text-muted-foreground uppercase mb-1.5 block">参画ステータス</label>
                                            <select 
                                                @change="updateMatching(biz.id, { status: ($event.target as HTMLSelectElement).value })"
                                                class="w-full text-xs border border-input bg-background rounded-md px-2 py-1.5 font-bold cursor-pointer focus:ring-2 ring-primary/20 outline-none"
                                            >
                                                <option value="requesting" :selected="biz.status === 'requesting'">見積依頼中</option>
                                                <option value="received" :selected="biz.status === 'received'">見積受領</option>
                                                <option value="ongoing" :selected="biz.status === 'ongoing'">施工・関与中</option>
                                                <option value="completed" :selected="biz.status === 'completed'">施工完了</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-[9px] font-bold text-muted-foreground uppercase mb-1.5 block">社内メモ</label>
                                            <div class="relative group/memo">
                                                <textarea 
                                                    @blur="updateMatching(biz.id, { memo: ($event.target as HTMLTextAreaElement).value })"
                                                    class="w-full text-[11px] bg-background border border-input rounded-md px-2 py-1.5 h-9 resize-none focus:h-24 transition-all duration-300 focus:ring-2 ring-primary/20 outline-none"
                                                    placeholder="交渉状況など..."
                                                >{{ biz.memo }}</textarea>
                                                <StickyNote class="absolute right-2 top-2 w-3 h-3 text-muted-foreground opacity-30 group-focus-within/memo:opacity-0" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col items-end gap-2 shrink-0">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon" class="h-8 w-8"><MoreVertical class="w-4 h-4 text-muted-foreground" /></Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem @click="removeBiz(biz.id)" class="text-destructive font-bold">紐付けを解除</DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                    <div class="text-[9px] text-muted-foreground font-medium flex items-center gap-1 mt-auto whitespace-nowrap">
                                        <Clock class="w-3 h-3" /> {{ biz.updated_at_human || '更新履歴なし' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-if="relatedBizs.length === 0" class="border-2 border-dashed border-border rounded-xl p-10 md:p-16 text-center text-muted-foreground bg-card/30">
                            <Building2 class="w-8 h-8 opacity-20 text-foreground mx-auto mb-4" />
                            <h3 class="text-base font-bold text-foreground mb-1">関連企業がありません</h3>
                            <p class="text-xs">右上の検索から企業を紐付けてください</p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </AppLayout>
</template>