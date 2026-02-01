<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link } from "@inertiajs/vue3";
import { Search, Plus, Building2, ArrowRight, Globe, Users, Phone, MapPin, Briefcase, ChevronRight } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

const props = defineProps({
    bizs: {
        type: Object,
        default: () => ({ data: [] })
    },
    filters: {
        type: Object,
        default: () => ({})
    },
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: '企業一覧', href: '/biz/index' },
];

const form = useForm({
    keyword: props.filters?.keyword ?? '',
    status: props.filters?.status ?? 'active', 
});

function submit() {
    form.get('/biz/index', { preserveState: true });
}

function setStatus(status: 'active' | 'all') {
    form.status = status;
    submit();
}
</script>

<template>
    <Head title="企業一覧" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6">
            
            <div class="flex items-center gap-1 mb-4 p-1 bg-muted/50 w-fit rounded-lg border shadow-sm">
                <button 
                    @click="setStatus('active')"
                    :class="form.status === 'active' ? 'bg-background shadow-sm text-primary' : 'text-muted-foreground hover:text-foreground'"
                    class="px-4 py-1.5 text-xs font-bold rounded-md transition-all flex items-center gap-2"
                >
                    <Users class="w-3.5 h-3.5" /> 案件関与中
                </button>
                <button 
                    @click="setStatus('all')"
                    :class="form.status === 'all' ? 'bg-background shadow-sm text-primary' : 'text-muted-foreground hover:text-foreground'"
                    class="px-4 py-1.5 text-xs font-bold rounded-md transition-all flex items-center gap-2"
                >
                    <Globe class="w-3.5 h-3.5" /> すべて
                </button>
            </div>

            <div class="mb-6 flex flex-col md:flex-row gap-3 items-stretch md:items-end bg-card p-4 rounded-xl border shadow-sm">
                <div class="flex-1">
                    <label class="text-[10px] font-bold uppercase text-muted-foreground mb-1 block ml-1">Company Search</label>
                    <div class="flex gap-2">
                        <Input v-model="form.keyword" placeholder="企業名で検索..." class="h-10" @keyup.enter="submit" />
                        <Button @click="submit" class="h-10 px-6">
                            <Search class="w-4 h-4 md:mr-2" /> <span class="hidden md:inline">検索</span>
                        </Button>
                    </div>
                </div>
                <Button as-child variant="outline" class="h-10 border-dashed border-primary text-primary hover:bg-primary/5">
                    <Link href="/biz/edit/0"><Plus class="w-4 h-4 mr-2" /> 新規登録</Link>
                </Button>
            </div>

            <div class="rounded-lg border bg-card shadow-sm overflow-hidden">
                <table class="w-full text-sm hidden md:table">
                    <thead class="bg-muted/30 border-b text-muted-foreground font-medium">
                        <tr>
                            <th class="h-12 px-4 text-left w-[350px]">企業情報</th>
                            <th class="h-12 px-4 text-left">現在の関与案件 / 取引状況</th>
                            <th class="h-12 px-4 text-right">操作</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="biz in bizs?.data" :key="biz.id" class="hover:bg-muted/5 transition-colors">
                            <td class="p-4 align-top">
                                <div class="flex items-start gap-3">
                                    <div class="p-2 bg-slate-100 rounded-lg text-slate-600 mt-1">
                                        <Building2 class="w-4 h-4" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-bold text-base text-slate-900 leading-tight mb-1">{{ biz.company_name }}</div>
                                        <div class="space-y-0.5">
                                            <div class="text-[11px] text-muted-foreground flex items-center gap-1">
                                                <MapPin class="w-3 h-3 shrink-0" /> {{ biz.address || '住所未登録' }}
                                            </div>
                                            <div class="text-[11px] text-blue-600 font-bold flex items-center gap-1">
                                                <Phone class="w-3 h-3 shrink-0 text-slate-400" /> {{ biz.phone_number || '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="p-4 align-top">
                                <div class="flex flex-col gap-3">
                                    <div v-if="biz.ongoing_projects?.length" class="grid grid-cols-1 gap-1.5">
                                        <div v-for="p in biz.ongoing_projects" :key="p.id" 
                                            class="group relative flex items-center justify-between bg-blue-50/50 hover:bg-blue-50 border border-blue-100 rounded-md px-3 py-1.5 transition-all">
                                            <div class="flex items-center gap-2 overflow-hidden">
                                                <div class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse shrink-0"></div>
                                                <span class="text-[11px] font-bold text-blue-900 truncate">{{ p.name }}</span>
                                            </div>
                                            <span class="text-[9px] font-bold text-blue-500/60 uppercase tracking-tighter ml-2 shrink-0">Ongoing</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center gap-3">
                                            <div v-if="biz.ongoing_count > 0" class="flex flex-col">
                                                <span class="text-[9px] text-muted-foreground font-bold uppercase">施工中</span>
                                                <span class="text-sm font-black text-blue-600 leading-none">{{ biz.ongoing_count }}<span class="text-[10px] ml-0.5">件</span></span>
                                            </div>
                                            <div v-if="biz.received_count > 0" class="flex flex-col border-l pl-3">
                                                <span class="text-[9px] text-muted-foreground font-bold uppercase">見積済</span>
                                                <span class="text-sm font-black text-emerald-600 leading-none">{{ biz.received_count }}<span class="text-[10px] ml-0.5">件</span></span>
                                            </div>
                                            <div v-if="biz.requesting_count > 0" class="flex flex-col border-l pl-3">
                                                <span class="text-[9px] text-muted-foreground font-bold uppercase">見積中</span>
                                                <span class="text-sm font-black text-amber-600 leading-none">{{ biz.requesting_count }}<span class="text-[10px] ml-0.5">件</span></span>
                                            </div>
                                        </div>
                                        
                                        <div class="ml-auto flex items-center gap-1.5 bg-slate-50 px-2 py-1 rounded border border-slate-100">
                                            <Briefcase class="w-3 h-3 text-slate-400" />
                                            <span class="text-[10px] font-bold text-slate-500">累計 {{ biz.projects_count }}</span>
                                        </div>
                                    </div>

                                    <div v-if="!biz.projects_count" class="text-slate-400 text-[11px] italic flex items-center gap-1">
                                        <span class="w-1 h-1 bg-slate-300 rounded-full"></span> 案件履歴なし
                                    </div>
                                </div>
                            </td>

                            <td class="p-4 text-right align-top">
                                <Button variant="ghost" size="sm" as-child class="group hover:bg-primary hover:text-white transition-all">
                                    <Link :href="`/biz/edit/${biz.id}`">
                                        詳細
                                        <ChevronRight class="w-4 h-4 ml-1 opacity-50 group-hover:opacity-100" />
                                    </Link>
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="md:hidden divide-y">
                    <div v-for="biz in bizs?.data" :key="biz.id" class="p-4 active:bg-slate-50">
                        <Link :href="`/biz/edit/${biz.id}`" class="block">
                            <div class="flex justify-between items-start mb-2">
                                <div class="font-bold text-base text-slate-900">{{ biz.company_name }}</div>
                                <div class="bg-blue-50 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded border border-blue-100">
                                    施工: {{ biz.ongoing_count }}
                                </div>
                            </div>
                            
                            <div class="space-y-2 mb-4">
                                <div v-for="p in biz.ongoing_projects" :key="p.id" class="text-[11px] font-bold text-blue-800 bg-blue-50/30 px-2 py-1 border-l-2 border-blue-500 flex justify-between">
                                    {{ p.name }}
                                    <span class="text-[8px] opacity-50">NOW</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between text-[11px]">
                                <div class="flex items-center gap-3 text-muted-foreground">
                                    <span class="flex items-center gap-1"><Phone class="w-3 h-3" /> {{ biz.phone_number || '-' }}</span>
                                    <span class="font-bold">累計: {{ biz.projects_count }}件</span>
                                </div>
                                <div class="text-primary font-bold flex items-center gap-1">
                                    DETAIL <ArrowRight class="w-3 h-3" />
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <div v-if="!bizs?.data?.length" class="p-16 text-center">
                    <Search class="w-10 h-10 mx-auto text-muted-foreground/30 mb-4" />
                    <p class="text-sm font-bold text-muted-foreground">該当する企業が見つかりませんでした。</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>