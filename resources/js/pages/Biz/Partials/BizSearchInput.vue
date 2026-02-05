<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { Search, Plus, X, Loader2, Filter, Check, MapPin, AlertCircle } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

const props = defineProps({
    projectId: { type: Number, required: true },
    relatedBizs: { type: Array, default: () => [] },
    projectAddress: { type: String, default: '' }
});

const isAlreadyAdded = (bizId: number) => {
    return props.relatedBizs.some((rb: any) => rb.id === bizId);
};

const isSearching = ref(false);
const isAdvanced = ref(false);
const isLoading = ref(false);
const results = ref([]);

const searchForm = ref({
    keyword: '',
    category: '', 
    minScore: '',
    minSales: '',
    maxSales: '',
    location: ''
});

// 業種が選択されているかどうかの判定
const isCategorySelected = computed(() => {
    return !!(searchForm.value.category && searchForm.value.category !== 'all');
});

const hasActiveFilters = computed(() => {
    return !!(
        isCategorySelected.value ||
        searchForm.value.location ||
        (isCategorySelected.value && searchForm.value.minScore) || // カテゴリ選択中のみP点バッジを出す
        searchForm.value.minSales ||
        searchForm.value.maxSales
    );
});

const clearFilter = (key: string) => {
    if (key === 'sales') {
        searchForm.value.minSales = '';
        searchForm.value.maxSales = '';
    } else if (key === 'category') {
        searchForm.value.category = 'all';
        searchForm.value.minScore = ''; // 業種解除時にP点もリセット
    } else {
        (searchForm.value as any)[key] = '';
    }
    fetchResults();
};

const formatMoney = (value: number | null) => {
    if (value === null || value === undefined) return '-';
    if (value >= 100000000) {
        return (value / 100000000).toLocaleString(undefined, { minimumFractionDigits: 1, maximumFractionDigits: 1 }) + '億円';
    }
    return (Math.floor(value / 10000)).toLocaleString() + '万円';
};

const toggleSearch = () => {
    isSearching.value = !isSearching.value;
    if (!isSearching.value) {
        searchForm.value = { keyword: '', category: '', minScore: '', minSales: '', maxSales: '', location: '' };
        results.value = [];
        isAdvanced.value = false;
    }
};

const fetchResults = async () => {
    const cleanKeyword = searchForm.value.keyword?.trim() || '';
    const cleanLocation = searchForm.value.location?.trim() || '';

    if (cleanKeyword.length < 2 && cleanLocation.length < 2 && !isCategorySelected.value) {
        results.value = [];
        return;
    }

    isLoading.value = true;
    try {
        const { category, minScore, minSales, maxSales } = searchForm.value;
        const params: any = {
            keyword: cleanKeyword,
            location: cleanLocation || null,
        };

        params.category = isCategorySelected.value ? category : null;
        // 業種が選択されている時だけP点をパラメータに含める
        params.min_score = isCategorySelected.value ? (minScore || null) : null;
        params.min_sales = minSales ? Number(minSales) * 100000000 : null;
        params.max_sales = maxSales ? Number(maxSales) * 100000000 : null;

        const response = await axios.get(`/api/biz/search`, { params });
        results.value = response.data;
    } catch (error) {
        console.error("検索エラー:", error);
        results.value = [];
    } finally {
        isLoading.value = false;
    }
};

watch(searchForm, (newForm, oldForm) => {
    // 業種が「指定なし」になったらP点を強制クリア
    if (oldForm.category && (newForm.category === 'all' || !newForm.category)) {
        searchForm.value.minScore = '';
    }
    if (isSearching.value) fetchResults();
}, { deep: true });

const addBiz = (biz: any) => {
    router.post(`/project/${props.projectId}/matching`, {
        biz_id: biz.id,
        role: '協力会社',
    }, {
        preserveScroll: true,
    });
};

onMounted(() => {
    if (props.projectAddress) {
        const match = props.projectAddress.match(/^.{2,3}[都道府県]/);
        if (match) {
            searchForm.value.location = match[0];
        }
    }
});

const categories = [
    '土木一式', '建築一式', '大工', '左官', 'とび・土工', 
    '石', '屋根', '電気', '管', 'タイル・れんが', 
    '鋼構造物', '鉄筋', '舗装', 'しゅんせつ', '板金', 
    'ガラス', '塗装', '防水', '内装仕上', '機械器具設置', 
    '熱絶縁', '電気通信', '造園', 'さく井', '建具', 
    '水道施設', '消防施設', '清掃', '解体', 'プレストレスト'
];
</script>

<template>
    <div class="relative inline-block text-left">
        <Button v-if="!isSearching" @click="toggleSearch" size="sm" class="rounded-full shadow-lg transition-all active:scale-95">
            <Plus class="w-4 h-4 mr-1" /> 企業を追加
        </Button>
        
        <Button v-else @click="toggleSearch" variant="outline" size="sm" class="rounded-full bg-background shadow-md h-9 w-9 p-0 border-2 border-primary text-primary z-[60] relative">
            <X class="w-5 h-5" />
        </Button>

        <div v-if="isSearching" 
             class="absolute top-12 right-0 w-[92vw] sm:w-[500px] z-50 bg-popover text-popover-foreground border border-border rounded-2xl shadow-2xl p-4 animate-in fade-in zoom-in-95 slide-in-from-top-2 origin-top-right">
            
            <div class="space-y-3">
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                        <Input v-model="searchForm.keyword" 
                               placeholder="企業名または許可番号..." 
                               class="pl-9 h-11 border-none shadow-none focus-visible:ring-1 bg-muted/50 text-base sm:text-sm" 
                               @keyup.enter="fetchResults"
                               auto-focus />
                    </div>
                    <Button @click="isAdvanced = !isAdvanced" variant="ghost" size="sm" :class="isAdvanced ? 'text-primary bg-primary/10' : 'text-muted-foreground'">
                        <Filter class="w-4 h-4 mr-1" />
                        詳細
                    </Button>
                </div>

                <div class="relative group">
                    <MapPin class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground group-focus-within:text-primary transition-colors" />
                    <Input v-model="searchForm.location" 
                           placeholder="場所（例：愛知県）" 
                           class="pl-9 h-9 text-xs bg-muted/30 border-dashed border-muted-foreground/30 focus-visible:ring-1" />
                </div>
            </div>

            <div v-if="hasActiveFilters" class="flex flex-wrap gap-1.5 mt-3 mb-1 animate-in slide-in-from-top-1">
                <div v-if="isCategorySelected" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold border border-blue-200">
                    {{ searchForm.category }}
                    <button @click.stop="clearFilter('category')"><X class="w-2.5 h-2.5" /></button>
                </div>
                <div v-if="isCategorySelected && searchForm.minScore" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-orange-100 text-orange-700 text-[10px] font-bold border border-orange-200">
                    P:{{ searchForm.minScore }}↑
                    <button @click.stop="clearFilter('minScore')"><X class="w-2.5 h-2.5" /></button>
                </div>
                <div v-if="searchForm.minSales || searchForm.maxSales" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-bold border border-emerald-200">
                    売上:{{ searchForm.minSales || 0 }}~{{ searchForm.maxSales || '∞' }}億
                    <button @click.stop="clearFilter('sales')"><X class="w-2.5 h-2.5" /></button>
                </div>
            </div>

            <div v-if="isAdvanced" class="mt-3 p-3 bg-muted/40 rounded-xl space-y-4 border border-border/50 animate-in slide-in-from-top-1">
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center px-1">
                        <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider">対象業種（指定するとP点等を表示）</label>
                        <span v-if="isCategorySelected" class="text-[9px] text-primary font-bold">適用中</span>
                    </div>
                    <Select v-model="searchForm.category">
                        <SelectTrigger :class="['h-9 text-xs transition-all', isCategorySelected ? 'bg-blue-50/50 border-blue-300 ring-1 ring-blue-100' : 'bg-background']">
                            <SelectValue placeholder="すべての業種（指定なし）" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">すべての業種（指定なし）</SelectItem>
                            <SelectItem v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div class="space-y-1 relative group">
                        <label :class="['text-[10px] font-bold ml-1 transition-colors flex items-center gap-1', 
                            !isCategorySelected ? 'text-muted-foreground/50' : (searchForm.minScore ? 'text-orange-600' : 'text-muted-foreground')]">
                            P点(以上)
                            <AlertCircle v-if="!isCategorySelected" class="w-2.5 h-2.5" />
                        </label>
                        <div class="relative">
                            <Input v-model="searchForm.minScore" 
                                type="number" 
                                :placeholder="isCategorySelected ? '指定なし' : '業種を選択'" 
                                :disabled="!isCategorySelected"
                                :class="['h-8 text-[10px] pr-6 transition-all', 
                                    !isCategorySelected ? 'bg-muted/50 border-muted-foreground/10 cursor-not-allowed opacity-60' : 
                                    (searchForm.minScore ? 'border-orange-400 bg-orange-50/30 ring-1 ring-orange-200' : 'bg-background')]" />
                            <button v-if="searchForm.minScore && isCategorySelected" @click="searchForm.minScore = ''" class="absolute right-1.5 top-1/2 -translate-y-1/2 text-orange-400 hover:text-orange-600"><X class="w-3 h-3" /></button>
                        </div>
                    </div>
                    <div class="space-y-1 relative group">
                        <label :class="['text-[10px] font-bold ml-1 transition-colors', searchForm.minSales ? 'text-emerald-600' : 'text-muted-foreground']">売上(億↑)</label>
                        <div class="relative">
                            <Input v-model="searchForm.minSales" type="number" placeholder="指定なし" 
                                :class="['h-8 text-xs pr-6 transition-all', searchForm.minSales ? 'border-emerald-400 bg-emerald-50/30 ring-1 ring-emerald-200' : 'bg-background']" />
                            <button v-if="searchForm.minSales" @click="searchForm.minSales = ''" class="absolute right-1.5 top-1/2 -translate-y-1/2 text-emerald-400 hover:text-emerald-600"><X class="w-3 h-3" /></button>
                        </div>
                    </div>
                    <div class="space-y-1 relative group">
                        <label :class="['text-[10px] font-bold ml-1 transition-colors', searchForm.maxSales ? 'text-emerald-600' : 'text-muted-foreground']">売上(億↓)</label>
                        <div class="relative">
                            <Input v-model="searchForm.maxSales" type="number" placeholder="指定なし" 
                                :class="['h-8 text-xs pr-6 transition-all', searchForm.maxSales ? 'border-emerald-400 bg-emerald-50/30 ring-1 ring-emerald-200' : 'bg-background']" />
                            <button v-if="searchForm.maxSales" @click="searchForm.maxSales = ''" class="absolute right-1.5 top-1/2 -translate-y-1/2 text-emerald-400 hover:text-emerald-600"><X class="w-3 h-3" /></button>
                        </div>
                    </div>
                </div>
                <p v-if="!isCategorySelected" class="text-[9px] text-muted-foreground/70 italic ml-1">※P点の絞り込みには業種の指定が必要です</p>
            </div>

            <div v-if="isLoading" class="flex justify-center p-8">
                <Loader2 class="w-6 h-6 animate-spin text-primary opacity-70" />
            </div>

            <div v-if="results.length > 0 && !isLoading" class="mt-4 border-t border-border max-h-80 overflow-y-auto pt-2 space-y-1 pr-1 custom-scrollbar">
                <button v-for="biz in results" :key="biz.id" @click="addBiz(biz)"
                    :disabled="isAlreadyAdded(biz.id)"
                    class="w-full text-left p-3 hover:bg-accent rounded-xl flex justify-between items-center group disabled:opacity-50 transition-all active:scale-[0.98]">
                    <div class="min-w-0 pr-2">
                        <div class="text-sm font-bold truncate flex items-center gap-2">
                            <span :class="isAlreadyAdded(biz.id) ? 'text-muted-foreground' : 'group-hover:text-primary'">{{ biz.company_name }}</span>
                            <span v-if="isAlreadyAdded(biz.id)" class="text-[9px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full font-medium shrink-0">追加済</span>
                        </div>
                        
                        <div class="text-[10px] text-muted-foreground truncate flex flex-wrap gap-x-2 gap-y-1 mt-1.5 items-center">
                            <span v-if="biz.company_total_sales" class="bg-emerald-50 text-emerald-700 px-1.5 py-0.5 rounded font-bold border border-emerald-100/50">
                                年商: {{ formatMoney(biz.company_total_sales) }}
                            </span>
                            <span v-if="isCategorySelected && biz.target_p_score" 
                                class="bg-orange-50 text-orange-700 px-1.5 py-0.5 rounded font-mono font-bold border border-orange-100">
                                {{ searchForm.category }} P:{{ biz.target_p_score }}
                            </span>
                            <span v-if="isCategorySelected && biz.category_sales" 
                                class="bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded font-bold border border-blue-100">
                                工種売上: {{ formatMoney(biz.category_sales) }}
                            </span>
                            <span class="truncate opacity-80 ml-1">{{ biz.address }}</span>
                        </div>
                    </div>
                    <div class="shrink-0 ml-2">
                        <div v-if="!isAlreadyAdded(biz.id)" class="bg-primary/10 p-1.5 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                            <Plus class="w-4 h-4 text-primary" />
                        </div>
                        <Check v-else class="w-5 h-5 text-emerald-500" />
                    </div>
                </button>
            </div>
            
            <div v-else-if="(searchForm.keyword.length >= 2 || searchForm.location) && !isLoading" class="p-10 text-center text-xs text-muted-foreground bg-muted/20 rounded-xl mt-4">
                <Search class="w-8 h-8 mx-auto mb-2 opacity-20" />
                <p class="font-bold">該当する企業が見つかりません</p>
                <p class="mt-1 opacity-70">条件を緩めるか、場所を書き換えてください</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: hsl(var(--muted)); border-radius: 10px; }
</style>