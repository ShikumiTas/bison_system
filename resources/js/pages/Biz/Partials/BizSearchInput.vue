<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { Search, Plus, X, Loader2, Filter, Check } from 'lucide-vue-next';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
// v4形式のインポート（.vueを付けない）
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
    // 親から案件の住所（または都道府県）をもらう
    projectAddress: { type: String, default: '' }
});

const isAlreadyAdded = (bizId: number) => {
    // 現在プロジェクトに紐付いている業者の中に、同じIDがあるかチェック
    return props.relatedBizs.some((rb: any) => rb.id === bizId);
};

// --- リアクティブ変数の定義（ここが漏れていると消えます） ---
const isSearching = ref(false);
const isAdvanced = ref(false);
const isLoading = ref(false);
const results = ref([]);

// 検索フォーム
const searchForm = ref({
    keyword: '',
    category: '土木一式',
    minScore: '',
    minSales: '',
    maxSales: '', // ★追加
    location: ''
});

// --- 関数定義 ---
const toggleSearch = () => {
    isSearching.value = !isSearching.value;
    if (!isSearching.value) {
        searchForm.value.keyword = '';
        results.value = [];
    }
};

const fetchResults = async () => {
    const query = searchForm.value.keyword;
    // キーワードが短く、かつ詳細モードでもない場合は検索しない
    if (query.length < 2 && !isAdvanced.value) {
        results.value = [];
        return;
    }

    isLoading.value = true;
    try {
        const response = await axios.get(`/api/biz/search`, {
            params: { 
                keyword: query,
                category: isAdvanced.value ? searchForm.value.category : null,
                location: isAdvanced.value ? searchForm.value.location : null,
                min_score: isAdvanced.value ? searchForm.value.minScore : null,
                min_sales: searchForm.value.minSales,
                max_sales: searchForm.value.maxSales // ★追加
            }
        });
        // console.log('検索結果データ:', response.data[0]); // 最初の1件の中身を確認
        results.value = response.data;
    } catch (error) {
        console.error("検索エラー:", error);
    } finally {
        isLoading.value = false;
    }
};

// フォームの変更を監視して検索実行
watch(searchForm, () => {
    fetchResults();
}, { deep: true });

const addBiz = (biz: any) => {
    router.post(`/project/${props.projectId}/matching`, {
        biz_id: biz.id,
        role: '協力会社', // 必要に応じてここも可変にできます
    }, {
        preserveScroll: true, // 画面の位置を維持
        onSuccess: () => {
            // toggleSearch(); <-- これをコメントアウトまたは削除！
            
            // 登録されたことを知らせる小さな演出を入れるなら
            // console.log(`${biz.company_name} を追加しました`);
            
            // オプション：登録した企業をリストから消すか「登録済」にしたい場合
            // biz.added = true; などのフラグを立てると親切です
        }
    });
};
// 画面読み込み時（または検索パネルを開いた時）の処理
onMounted(() => {
    if (props.projectAddress) {
        // 住所から都道府県を抽出（「愛知県名古屋市...」から「愛知県」を抽出）
        const match = props.projectAddress.match(/^.{2,3}[都道府県]/);
        if (match) {
            searchForm.value.location = match[0];
            // 初期状態で検索を実行させたい場合はここでfetchResults()を呼ぶか
            // searchFormのwatchが検知して自動で走ります
        }
    }
});
// 経審CSVの並び順に基づいた全30業種
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
    <div class="relative">
        <Button v-if="!isSearching" @click="toggleSearch" size="sm" class="rounded-full shadow-lg transition-transform active:scale-95">
            <Plus class="w-4 h-4 mr-1" /> 企業を追加
        </Button>
        
        <Button v-else @click="toggleSearch" variant="ghost" size="sm" class="rounded-full h-8 w-8 p-0">
            <X class="w-4 h-4" />
        </Button>

        <div v-if="isSearching" 
             class="absolute top-10 right-0 w-[450px] z-50 bg-popover text-popover-foreground border border-border rounded-xl shadow-2xl p-3 animate-in fade-in zoom-in-95">
            
            <div class="flex items-center gap-2 mb-3">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                    <Input v-model="searchForm.keyword" 
                           placeholder="企業名または許可番号を入力..." 
                           class="pl-9 h-10 border-none shadow-none focus-visible:ring-0 bg-muted/40 text-sm" 
                           auto-focus />
                </div>
                <Button @click="isAdvanced = !isAdvanced" variant="outline" size="sm" :class="isAdvanced ? 'bg-primary/10 text-primary border-primary/20' : ''">
                    <Filter class="w-4 h-4 mr-1" />
                    詳細
                </Button>
            </div>

            <div v-if="isAdvanced" class="p-3 bg-muted/30 rounded-lg mb-3 space-y-3 border border-border/50">
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-muted-foreground ml-1">対象業種</label>
                        <Select v-model="searchForm.category">
                            <SelectTrigger class="h-8 text-xs bg-background">
                                <SelectValue placeholder="業種選択" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-muted-foreground ml-1">場所</label>
                        <Input v-model="searchForm.location" placeholder="愛知県..." class="h-8 text-xs bg-background" />
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-muted-foreground ml-1">P点(以上)</label>
                        <Input v-model="searchForm.minScore" type="number" placeholder="800" class="h-8 text-xs bg-background" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-muted-foreground ml-1">売上(億円以上)</label>
                        <Input v-model="searchForm.minSales" type="number" placeholder="1" class="h-8 text-xs bg-background" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-muted-foreground ml-1">売上(億円以下)</label>
                        <Input v-model="searchForm.maxSales" type="number" placeholder="50" class="h-8 text-xs bg-background" />
                    </div>
                </div>
            </div>

            <div v-if="isLoading" class="flex justify-center p-6">
                <Loader2 class="w-6 h-6 animate-spin text-primary" />
            </div>

            <div v-if="results.length > 0 && !isLoading" class="mt-2 border-t border-border max-h-80 overflow-y-auto pt-2 space-y-1">
                <button v-for="biz in results" :key="biz.id" @click="addBiz(biz)"
                    :disabled="isAlreadyAdded(biz.id)"
                    class="w-full text-left p-2.5 hover:bg-accent rounded-lg flex justify-between items-center group disabled:opacity-60 transition-colors">
                    <div class="min-w-0 pr-2">
                        <div class="text-sm font-bold truncate flex items-center gap-2">
                            <span :class="isAlreadyAdded(biz.id) ? 'text-muted-foreground' : 'group-hover:text-primary'">{{ biz.company_name }}</span>
                            <span v-if="isAlreadyAdded(biz.id)" class="text-[9px] bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full font-medium">追加済</span>
                        </div>
                        <div class="text-[10px] text-muted-foreground truncate flex gap-3 mt-1 items-center">
                            <span class="bg-secondary px-1.5 py-0.5 rounded font-mono">P:{{ biz.target_p_score ?? '-' }}</span>
                            <span v-if="biz.target_sales" class="text-blue-600 font-bold bg-blue-50 px-1.5 py-0.5 rounded">
                                売上:{{ (biz.target_sales / 10000).toFixed(1) }}億円
                            </span>
                            <span class="truncate">| {{ biz.address }}</span>
                        </div>
                    </div>
                    <Plus v-if="!isAlreadyAdded(biz.id)" class="w-4 h-4 text-primary opacity-0 group-hover:opacity-100 transition-opacity" />
                    <Check v-else class="w-4 h-4 text-emerald-500" />
                </button>
            </div>
            
            <div v-else-if="(searchForm.keyword.length >= 2 || searchForm.location) && !isLoading" class="p-8 text-center text-xs text-muted-foreground">
                <p>該当する企業が見つかりません</p>
                <p class="mt-1 opacity-70">条件を緩めてお試しください</p>
            </div>
        </div>
    </div>
</template>