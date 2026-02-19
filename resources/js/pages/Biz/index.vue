<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, Link, router } from "@inertiajs/vue3";
import {
    Search, Plus, Building2, ArrowRight, Globe, Users,
    Phone, MapPin, Briefcase, ChevronRight, ChevronLeft, MoreHorizontal,
    Trash2
} from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

const props = defineProps({
    bizs: {
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
    { title: '企業一覧', href: '/biz/index' },
];

const form = useForm({
    keyword: props.filters?.keyword ?? '',
    status: props.filters?.status ?? 'active',
});

function submit() {
    form.get('/biz/index', { preserveState: true, preserveScroll: true });
}

function setStatus(status: 'active' | 'all') {
    form.status = status;
    submit();
}

function confirmDelete(id: number) {
    if (confirm('この企業データを削除しますか？\n※案件データ自体は削除されません。')) {
        router.delete(`/biz/${id}`, {
            preserveScroll: true,
        });
    }
}

/**
 * ペジネーションラベルの翻訳と変換
 */
const getLinkLabel = (label: string) => {
    if (label.includes('Previous') || label.includes('previous')) return 'prev';
    if (label.includes('Next') || label.includes('next')) return 'next';
    return label;
};
</script>

<template>

    <Head title="企業一覧" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6 text-left">

            <div class="flex items-center gap-1 mb-4 p-1 bg-muted/50 w-fit rounded-lg border shadow-sm">
                <button @click="setStatus('active')"
                    :class="form.status === 'active' ? 'bg-background shadow-sm text-primary' : 'text-muted-foreground hover:text-foreground'"
                    class="px-5 py-1.5 text-xs font-black rounded-md transition-all flex items-center gap-2">
                    <Users class="w-3.5 h-3.5" /> 案件関与中
                </button>
                <button @click="setStatus('all')"
                    :class="form.status === 'all' ? 'bg-background shadow-sm text-primary' : 'text-muted-foreground hover:text-foreground'"
                    class="px-5 py-1.5 text-xs font-black rounded-md transition-all flex items-center gap-2">
                    <Globe class="w-3.5 h-3.5" /> すべて表示
                </button>
            </div>

            <div
                class="mb-6 flex flex-col md:flex-row gap-3 items-stretch md:items-end bg-card p-4 rounded-xl border shadow-sm">
                <div class="flex-1">
                    <label
                        class="text-[10px] font-bold text-muted-foreground mb-1 block ml-1 tracking-widest text-left">企業を絞り込む</label>
                    <div class="flex gap-2">
                        <Input v-model="form.keyword" placeholder="企業名・電話番号・住所..." class="h-10 text-sm font-medium"
                            @keyup.enter="submit" />
                        <Button @click="submit" class="h-10 px-6 font-bold">
                            <Search class="w-4 h-4 md:mr-2" /> <span class="hidden md:inline">検索する</span>
                        </Button>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border bg-card shadow-md overflow-hidden">
                <table class="w-full text-sm hidden md:table">
                    <thead class="bg-muted/50 border-b text-muted-foreground">
                        <tr>
                            <th class="h-12 px-5 text-left font-bold text-[11px] w-[320px]">企業基本情報</th>
                            <th class="h-12 px-4 text-left font-bold text-[11px]">現在の稼働状況 / 取引実績</th>
                            <th class="h-12 px-5 text-right font-bold text-[11px] w-[140px]">操作</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="biz in bizs?.data" :key="biz.id" class="hover:bg-muted/5 transition-colors group">
                            <td class="p-5 align-top text-left">
                                <div class="flex items-start gap-4">
                                    <div
                                        class="p-2.5 bg-muted rounded-xl text-muted-foreground shrink-0 border border-border/50">
                                        <Building2 class="w-5 h-5" />
                                    </div>
                                    <div class="min-w-0">
                                        <div
                                            class="font-bold text-[15px] text-foreground leading-snug mb-2 group-hover:text-primary transition-colors text-left">
                                            {{ biz.company_name }}</div>
                                        <div class="space-y-1">
                                            <div
                                                class="text-[11px] text-muted-foreground flex items-center gap-1.5 font-medium">
                                                <MapPin class="w-3.5 h-3.5 shrink-0 opacity-60" /> {{ biz.address ||
                                                '住所未登録' }}
                                            </div>
                                            <div
                                                class="text-[11px] text-blue-600 dark:text-blue-400 font-bold flex items-center gap-1.5">
                                                <Phone class="w-3.5 h-3.5 shrink-0 opacity-60 text-muted-foreground" />
                                                {{ biz.phone_number || '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="p-5 align-top">
                                <div class="flex flex-col gap-4">
                                    <div v-if="biz.ongoing_projects?.length" class="flex flex-wrap gap-2">
                                        <div v-for="p in biz.ongoing_projects" :key="p.id"
                                            class="flex items-center gap-2 bg-blue-500/10 border border-blue-500/20 rounded-full px-3 py-1">
                                            <div class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse shrink-0">
                                            </div>
                                            <span
                                                class="text-[11px] font-black text-blue-700 dark:text-blue-300 truncate max-w-[180px]">{{
                                                p.name }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-5 tabular-nums">
                                        <div class="flex items-center gap-3">
                                            <div class="flex flex-col border-l-2 border-blue-500 pl-3 text-left">
                                                <span
                                                    class="text-[9px] text-muted-foreground font-black mb-0.5">施工中</span>
                                                <span
                                                    class="text-[15px] font-black text-blue-600 dark:text-blue-400 leading-none">
                                                    {{ biz.ongoing_count ?? 0 }}<span
                                                        class="text-[10px] ml-0.5 font-bold text-muted-foreground">件</span>
                                                </span>
                                            </div>
                                            <div class="flex flex-col border-l-2 border-emerald-500 pl-3 text-left">
                                                <span
                                                    class="text-[9px] text-muted-foreground font-black mb-0.5">見積済</span>
                                                <span
                                                    class="text-[15px] font-black text-emerald-600 dark:text-emerald-400 leading-none">
                                                    {{ biz.received_count ?? 0 }}<span
                                                        class="text-[10px] ml-0.5 font-bold text-muted-foreground">件</span>
                                                </span>
                                            </div>
                                            <div class="flex flex-col border-l-2 border-amber-500 pl-3 text-left">
                                                <span
                                                    class="text-[9px] text-muted-foreground font-black mb-0.5">引合中</span>
                                                <span
                                                    class="text-[15px] font-black text-amber-600 dark:text-amber-400 leading-none">
                                                    {{ biz.requesting_count ?? 0 }}<span
                                                        class="text-[10px] ml-0.5 font-bold text-muted-foreground">件</span>
                                                </span>
                                            </div>
                                        </div>
                                        <div
                                            class="ml-auto flex items-center gap-2 bg-muted/50 px-3 py-1.5 rounded-lg border border-border/50">
                                            <Briefcase class="w-3.5 h-3.5 text-muted-foreground/60" />
                                            <span class="text-[11px] font-black text-muted-foreground">取引累計：{{
                                                biz.projects_count ?? 0 }}件</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="p-5 text-right align-middle">
                                <div class="flex items-center justify-end gap-2">
                                    <Button variant="ghost" size="icon" @click="confirmDelete(biz.id)"
                                        class="rounded-full transition-all duration-200 opacity-0 group-hover:opacity-100 hover:bg-red-50 hover:text-red-600 text-red-400 border border-transparent hover:border-red-100">
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                    <Button variant="ghost" size="icon" as-child
                                        class="rounded-full hover:bg-primary hover:text-white transition-all shadow-sm border">
                                        <Link :href="`/biz/edit/${biz.id}`">
                                            <ChevronRight class="w-5 h-5" />
                                        </Link>
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="md:hidden divide-y">
                    <div v-for="biz in bizs?.data" :key="biz.id" class="relative transition-colors">
                        <button @click="confirmDelete(biz.id)"
                            class="absolute top-4 right-4 z-20 p-2.5 rounded-full bg-red-50 text-red-400 active:bg-red-100 active:text-red-600 transition-colors border border-red-100/50 shadow-sm">
                            <Trash2 class="w-4 h-4" />
                        </button>

                        <Link :href="`/biz/edit/${biz.id}`" class="block p-5 active:bg-muted/50 space-y-4 text-left">
                            <div class="flex justify-between items-start">
                                <div class="font-black text-[16px] text-foreground leading-tight pr-12 shrink">{{
                                    biz.company_name }}</div>
                            </div>

                            <div class="flex flex-wrap gap-1.5">
                                <div
                                    class="bg-blue-500/10 text-blue-700 dark:text-blue-400 text-[10px] font-black px-2 py-1 rounded-md border border-blue-500/20 flex items-center gap-1">
                                    <span class="opacity-60 font-bold">施工:</span>{{ biz.ongoing_count ?? 0 }}
                                </div>
                                <div
                                    class="bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 text-[10px] font-black px-2 py-1 rounded-md border border-emerald-500/20 flex items-center gap-1">
                                    <span class="opacity-60 font-bold">見積:</span>{{ biz.received_count ?? 0 }}
                                </div>
                                <div
                                    class="bg-amber-500/10 text-amber-700 dark:text-amber-400 text-[10px] font-black px-2 py-1 rounded-md border border-amber-500/20 flex items-center gap-1">
                                    <span class="opacity-60 font-bold">引合:</span>{{ biz.requesting_count ?? 0 }}
                                </div>
                                <div
                                    class="bg-muted text-muted-foreground text-[10px] font-black px-2 py-1 rounded-md border flex items-center gap-1 ml-auto">
                                    <Briefcase class="w-3 h-3 opacity-50" />{{ biz.projects_count ?? 0 }}
                                </div>
                            </div>

                            <div v-if="biz.ongoing_projects?.length"
                                class="bg-blue-500/5 border border-blue-500/10 rounded-lg px-3 py-2 flex items-center gap-3">
                                <div class="w-1.5 h-1.5 rounded-full bg-blue-500 shrink-0"></div>
                                <span
                                    class="text-[11px] font-bold text-blue-800 dark:text-blue-300 truncate flex-1 text-left">{{
                                    biz.ongoing_projects[0].name }}</span>
                                <span v-if="biz.ongoing_projects.length > 1"
                                    class="text-[10px] text-blue-500/60 font-bold shrink-0">+{{
                                    biz.ongoing_projects.length - 1 }}</span>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-dashed">
                                <span class="flex items-center gap-1.5 text-[11px] text-muted-foreground font-bold">
                                    <Phone class="w-3.5 h-3.5 opacity-50" /> {{ biz.phone_number || '-' }}
                                </span>
                                <div
                                    class="text-primary font-black text-[11px] flex items-center gap-1 uppercase tracking-wider">
                                    詳細
                                    <ArrowRight class="w-3.5 h-3.5" />
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>

                <div v-if="bizs?.links?.length > 3"
                    class="px-5 py-4 border-t bg-muted/10 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="text-[11px] font-bold text-muted-foreground tracking-widest">
                        全 {{ bizs.total }} 件中 {{ bizs.from }} 〜 {{ bizs.to }} 件を表示
                    </div>

                    <nav class="flex items-center gap-1">
                        <template v-for="(link, key) in bizs.links" :key="key">
                            <Button v-if="getLinkLabel(link.label) === 'prev'" variant="ghost" size="sm" as-child
                                class="h-8 px-2 md:w-8 md:p-0" :class="!link.url && 'pointer-events-none opacity-20'">
                                <Link :href="link.url || '#'" preserve-scroll>
                                    <ChevronLeft class="h-4 w-4" /><span class="md:hidden text-[10px] ml-1">前へ</span>
                                </Link>
                            </Button>

                            <Button v-else-if="getLinkLabel(link.label) === 'next'" variant="ghost" size="sm" as-child
                                class="h-8 px-2 md:w-8 md:p-0" :class="!link.url && 'pointer-events-none opacity-20'">
                                <Link :href="link.url || '#'" preserve-scroll><span
                                        class="md:hidden text-[10px] mr-1">次へ</span>
                                    <ChevronRight class="h-4 w-4" />
                                </Link>
                            </Button>

                            <Button v-else-if="link.label !== '...'" :variant="link.active ? 'outline' : 'ghost'"
                                size="sm" as-child class="h-8 w-8 p-0 text-[11px] font-bold" :class="[
                                    link.active ? 'bg-background shadow-sm border-primary/20 text-primary font-black' : 'text-muted-foreground',
                                    // スマホでは現在ページ以外の一部を隠すロジック（オプション）
                                    !link.active && !['1', String(bizs.last_page)].includes(link.label) && Math.abs(Number(link.label) - bizs.current_page) > 1 ? 'hidden md:flex' : 'flex'
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