<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

use App\UseCases\Biz\IndexAction;
use App\UseCases\Biz\FormAction;

class BizController extends Controller
{
    public function index(Request $request, IndexAction $case)
    {
        $post = $request->all();

        list($bizs, ) = $case($post);

        return Inertia::render('Biz/index', [
            'bizs' => $bizs,
        ]);
    }

    public function edit(Request $request, FormAction $case, $id = 0)
    {
        // UseCaseを実行して、企業・評価スコア・参画案件をまとめて取得
        // ※ $id=0 の場合は新規作成用の空モデルを返す想定
        $biz = $case($id);

        return Inertia::render('Biz/form', [
            'biz' => $biz,
            // 必要に応じて、プルダウン用のマスタデータなどを追加
        ]);
    }

    // public function search(Request $request)
    // {
    //     // 1. 基本情報の選択
    //     $query = \App\Models\Biz::query()->select('bizs.*');

    //     // 2. キーワード検索（企業名 or 許可番号）
    //     if ($request->filled('keyword')) {
    //         $keyword = $request->query('keyword');
    //         $query->where(function($q) use ($keyword) {
    //             $q->where('company_name', 'like', "%{$keyword}%")
    //             ->orWhere('permit_id', 'like', "%{$keyword}%");
    //         });
    //     }

    //     // 3. 場所の検索（都道府県・市区町村）
    //     if ($request->filled('location')) {
    //         $query->where('address', 'like', "%{$request->location}%");
    //     }

    //     // 4. 業種および詳細フィルタ（P点・売上）
    //     // 業種が未指定でもデフォルトで「土木一式」などを対象にする運用がスムーズです
    //     $category = $request->get('category', '土木一式');

    //     // 表示用データの取得（target_p_score, target_sales）
    //     $query->addSelect([
    //         'target_p_score' => \App\Models\BizScore::select('p_score')
    //             ->whereColumn('biz_id', 'bizs.id')
    //             ->where('work_category', $category)
    //             ->limit(1),
    //         'target_sales' => \App\Models\BizScore::select('avg_sales')
    //             ->whereColumn('biz_id', 'bizs.id')
    //             ->where('work_category', $category)
    //             ->limit(1)
    //     ]);

    //     // リレーション先の詳細条件で絞り込み
    //     $query->whereHas('scores', function ($q) use ($category, $request) {
    //         $q->where('work_category', $category);

    //         // P点（以上）フィルタ
    //         if ($request->filled('min_score')) {
    //             $q->where('p_score', '>=', $request->min_score);
    //         }

    //         // 売上（以上）フィルタ
    //         if ($request->filled('min_sales')) {
    //             $q->where('avg_sales', '>=', (int)$request->min_sales * 10000);
    //         }

    //         // 売上（以下）フィルタ ★追加
    //         if ($request->filled('max_sales')) {
    //             $q->where('avg_sales', '<=', (int)$request->max_sales * 10000);
    //         }
    //     });

    //     // 5. ソート：売上の降順を優先
    //     $query->orderBy('target_sales', 'desc')
    //         ->orderBy('target_p_score', 'desc')
    //         ->orderBy('id', 'asc');

    //     $bizs = $query->limit(20)->get();

    //     return response()->json($bizs);
    // }
    // public function search(Request $request)
    // {
    //     // 1. 基本セレクト
    //     $query = \App\Models\Biz::query()->select('bizs.*');

    //     // 2. 場所（東京都など）
    //     if ($request->filled('location')) {
    //         $query->where('address', 'like', "%{$request->location}%");
    //     }

    //     // 3. キーワード（企業名）
    //     if ($request->filled('keyword')) {
    //         $keyword = $request->keyword;
    //         $query->where(function($q) use ($keyword) {
    //             $q->where('company_name', 'like', "%{$keyword}%")
    //             ->orWhere('permit_id', 'like', "%{$keyword}%");
    //         });
    //     }

    //     // 4. 業種と数値詳細（ここがフィルタの核心）
    //     $category = $request->get('category', '土木一式');

    //     // 表示用サブクエリ
    //     $query->addSelect([
    //         'target_p_score' => \App\Models\BizScore::select('p_score')
    //             ->whereColumn('biz_id', 'bizs.id')
    //             ->where('work_category', $category)
    //             ->latest()->limit(1),
    //         'target_sales' => \App\Models\BizScore::select('avg_sales')
    //             ->whereColumn('biz_id', 'bizs.id')
    //             ->where('work_category', $category)
    //             ->latest()->limit(1)
    //     ]);

    //     // ★ フィルタを適用：指定した業種で「かつ」数値条件を満たすレコードがあるか
    //     $query->whereHas('scores', function ($q) use ($category, $request) {
    //         $q->where('work_category', $category);

    //         // P点フィルタ
    //         if ($request->filled('min_score')) {
    //             $q->where('p_score', '>=', (int)$request->min_score);
    //         }

    //         // 売上（以上）
    //         if ($request->filled('min_sales')) {
    //             $q->where('avg_sales', '>=', (int)$request->min_sales * 10000);
    //         }

    //         // 売上（以下）
    //         if ($request->filled('max_sales')) {
    //             $q->where('avg_sales', '<=', (int)$request->max_sales * 10000);
    //         }
    //     });

    //     // 5. ソート
    //     $query->orderBy('target_sales', 'desc');

    //     return response()->json($query->limit(20)->get());
    // }

    public function search(Request $request)
    {
        $query = \App\Models\Biz::query()->select('bizs.*');

        // 場所・キーワード（ここは変更なし）
        if ($request->filled('location')) { $query->where('address', 'like', "%{$request->location}%"); }
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('company_name', 'like', "%{$keyword}%")->orWhere('permit_id', 'like', "%{$keyword}%");
            });
        }

        // --- 修正箇所：デフォルト値を null にし、カテゴリがある時だけ AND 条件にする ---
        $category = $request->get('category'); // '土木一式' を消して null 許容に

        // 表示用サブクエリ（ここはカテゴリが null でも動くように latest() を維持）
        $query->addSelect([
            'target_p_score' => \App\Models\BizScore::select('p_score')
                ->whereColumn('biz_id', 'bizs.id')
                ->when($category, fn($q) => $q->where('work_category', $category)) // カテゴリがあれば絞る
                ->latest()->limit(1),
            'target_sales' => \App\Models\BizScore::select('avg_sales')
                ->whereColumn('biz_id', 'bizs.id')
                ->when($category, fn($q) => $q->where('work_category', $category)) // カテゴリがあれば絞る
                ->latest()->limit(1)
        ]);

        // カテゴリが指定されている時、または詳細条件がある時だけ絞り込み
        if ($category || $request->anyFilled(['min_score', 'min_sales', 'max_sales'])) {
            $query->whereHas('scores', function ($q) use ($category, $request) {
                if ($category) {
                    $q->where('work_category', $category);
                }
                if ($request->filled('min_score')) { $q->where('p_score', '>=', (int)$request->min_score); }
                if ($request->filled('min_sales')) { $q->where('avg_sales', '>=', (int)$request->min_sales * 10000); }
                if ($request->filled('max_sales')) { $q->where('avg_sales', '<=', (int)$request->max_sales * 10000); }
            });
        }
        // -------------------------------------------------------------------

        $query->orderBy('target_sales', 'desc');
        return response()->json($query->limit(20)->get());
    }
}
