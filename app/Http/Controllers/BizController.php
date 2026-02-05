<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

use App\UseCases\Biz\IndexAction;
use App\UseCases\Biz\FormAction;

use App\Models\Biz;

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

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $biz = Biz::findOrFail($id);

            // 1. 中間テーブル（matches）の紐付けのみ解除
            // これで Project モデルのレコードは一切削除されません
            $biz->projects()->detach();

            // 2. 企業固有の関連データを削除
            $biz->scores()->delete();
            $biz->financial()->delete();
            // $biz->comments()->delete();
            // matchesレコードを直接消したい場合はこちらも（detachで消えない場合）
            $biz->matches()->delete(); 

            // 3. 企業本体を削除
            $biz->delete();
        });

        return redirect()->route('biz.index')->with('message', '企業データを削除しました。');
    }

    public function search(Request $request)
    {
        $query = \App\Models\Biz::query();

        try {
            $query->select('bizs.*');
            $category = $request->get('category');

            // 1. 【表示用】常に会社全体の年商を取得
            $query->addSelect([
                'company_total_sales' => \App\Models\BizFinancial::select('total_net_sales')
                    ->whereColumn('biz_id', 'bizs.id')
                    ->limit(1)
            ]);

            // 2. 【条件分岐】業種指定の有無で絞り込み対象を変える
            if ($category && $category !== 'all') {
                // --- 業種指定あり：特定の工種売上(avg_sales)とP点で絞り込む ---
                $query->addSelect([
                    'target_p_score' => \App\Models\BizScore::select('p_score')
                        ->whereColumn('biz_id', 'bizs.id')
                        ->where('work_category', $category)
                        ->latest()->limit(1),
                    'category_sales' => \App\Models\BizScore::select('avg_sales')
                        ->whereColumn('biz_id', 'bizs.id')
                        ->where('work_category', $category)
                        ->latest()->limit(1),
                ]);

                $query->whereHas('scores', function ($q) use ($category, $request) {
                    $q->where('work_category', $category);
                    
                    // 業種売上(avg_sales)で絞り込み
                    if ($request->filled('min_sales')) {
                        $q->where('avg_sales', '>=', (float)$request->min_sales);
                    }
                    if ($request->filled('max_sales')) {
                        $q->where('avg_sales', '<=', (float)$request->max_sales);
                    }
                    // P点で絞り込み
                    if ($request->filled('min_score')) {
                        $q->where('p_score', '>=', (int)$request->min_score);
                    }
                });

                // 並び替え：工種売上順
                $query->orderByRaw('category_sales IS NULL ASC, category_sales DESC');

            } else {
                // --- 業種指定なし：会社全体の年商(total_net_sales)で絞り込む ---
                if ($request->filled('min_sales') || $request->filled('max_sales')) {
                    $query->whereHas('financial', function ($q) use ($request) {
                        if ($request->filled('min_sales')) {
                            $q->where('total_net_sales', '>=', (float)$request->min_sales);
                        }
                        if ($request->filled('max_sales')) {
                            $q->where('total_net_sales', '<=', (float)$request->max_sales);
                        }
                    });
                }
                // 並び替え：会社全体売上順
                $query->orderByRaw('company_total_sales IS NULL ASC, company_total_sales DESC');
            }

            // 3. 基本検索（場所・キーワード）
            if ($request->filled('location')) {
                $query->where('address', 'like', "%{$request->location}%");
            }
            if ($request->filled('keyword')) {
                $keyword = $request->keyword;
                $query->where(function($q) use ($keyword) {
                    $q->where('company_name', 'like', "%{$keyword}%")
                    ->orWhere('permit_id', 'like', "%{$keyword}%");
                });
            }

            return response()->json($query->limit(20)->get());

        } catch (\Exception $e) {
            \Log::error("Search Error: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
