<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { Search, Plus, X, Loader2, Filter, Check, MapPin } from 'lucide-vue-next';
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

// 検索窓の開閉：状態をリセットし、確実に切り替える
const toggleSearch = () => {
    isSearching.value = !isSearching.value;
    if (!isSearching.value) {
        searchForm.value.keyword = '';
        searchForm.value.category = '';
        results.value = [];
        isAdvanced.value = false;
    }
};

const fetchResults = async () => {
    const cleanKeyword = searchForm.value.keyword?.trim() || '';
    const cleanLocation = searchForm.value.location?.trim() || '';

    if (cleanKeyword.length < 2 && cleanLocation.length < 2) {
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

        if (isAdvanced.value) {
            // "all" が選択されている場合は null を送って全件対象にする
            params.category = (category && category !== 'all') ? category : null;
            params.min_score = minScore || null;
            params.min_sales = minSales || null;
            params.max_sales = maxSales || null;
        }

        const response = await axios.get(`/api/biz/search`, { params });
        results.value = response.data;
    } catch (error) {
        console.error("検索エラー:", error);
        results.value = [];
    } finally {
        isLoading.value = false;
    }
};

// フォーム変更の監視
watch(searchForm, () => {
    if (isSearching.value) fetchResults();
}, { deep: true });

// 詳細モードの切り替えでも再検索
watch(isAdvanced, () => {
    fetchResults();
});

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
             class="absolute top-12 right-0 w-[92vw] sm:w-[450px] z-50 bg-popover text-popover-foreground border border-border rounded-2xl shadow-2xl p-4 animate-in fade-in zoom-in-95 slide-in-from-top-2 origin-top-right">
            
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

            <div v-if="isAdvanced" class="mt-3 p-3 bg-muted/40 rounded-xl space-y-3 border border-border/50 animate-in slide-in-from-top-1">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-muted-foreground uppercase tracking-wider ml-1">対象業種</label>
                    <Select v-model="searchForm.category">
                        <SelectTrigger class="h-9 text-xs bg-background">
                            <SelectValue placeholder="すべての業種（指定なし）" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">すべての業種（指定なし）</SelectItem>
                            <SelectItem v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-muted-foreground ml-1">P点(以上)</label>
                        <Input v-model="searchForm.minScore" type="number" placeholder="800" class="h-8 text-xs bg-background" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-muted-foreground ml-1">売上(億↑)</label>
                        <Input v-model="searchForm.minSales" type="number" placeholder="1" class="h-8 text-xs bg-background" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-muted-foreground ml-1">売上(億↓)</label>
                        <Input v-model="searchForm.maxSales" type="number" placeholder="50" class="h-8 text-xs bg-background" />
                    </div>
                </div>
            </div>

            <div v-if="isLoading" class="flex justify-center p-8">
                <Loader2 class="w-6 h-6 animate-spin text-primary opacity-70" />
            </div>

            <div v-if="results.length > 0 && !isLoading" class="mt-4 border-t border-border max-h-72 overflow-y-auto pt-2 space-y-1 pr-1 custom-scrollbar">
                <button v-for="biz in results" :key="biz.id" @click="addBiz(biz)"
                    :disabled="isAlreadyAdded(biz.id)"
                    class="w-full text-left p-3 hover:bg-accent rounded-xl flex justify-between items-center group disabled:opacity-50 transition-all active:scale-[0.98]">
                    <div class="min-w-0 pr-2">
                        <div class="text-sm font-bold truncate flex items-center gap-2">
                            <span :class="isAlreadyAdded(biz.id) ? 'text-muted-foreground' : 'group-hover:text-primary'">{{ biz.company_name }}</span>
                            <span v-if="isAlreadyAdded(biz.id)" class="text-[9px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full font-medium shrink-0">追加済</span>
                        </div>
                        <div class="text-[10px] text-muted-foreground truncate flex flex-wrap gap-x-3 gap-y-1 mt-1.5 items-center">
                            <span class="bg-secondary px-1.5 py-0.5 rounded font-mono text-secondary-foreground">P:{{ biz.target_p_score ?? '-' }}</span>
                            <span v-if="biz.target_sales" class="text-blue-700 font-bold bg-blue-50 px-1.5 py-0.5 rounded">
                                {{ (biz.target_sales / 10000).toFixed(1) }}億円
                            </span>
                            <span class="truncate opacity-80">{{ biz.address }}</span>
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
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background-color: hsl(var(--muted));
  border-radius: 10px;
}
</style>