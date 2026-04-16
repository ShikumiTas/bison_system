<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

use App\UseCases\Project\IndexAction;
use App\UseCases\Project\FormAction;
use App\UseCases\Project\UpdateAction;
use App\UseCases\Project\MatchingAction;
use App\UseCases\Project\UnmatchingAction;

class ProjectController extends Controller
{
    public function index(Request $request, IndexAction $case)
    {
        // リクエストパラメータを取得
        $params = $request->all();
        
        list($projects, $extraData) = $case($params);

        return Inertia::render('Project/index', [
            'projects' => $projects,
            'filters'  => $request->only(['keyword', 'status']), // ここを追加
        ]);
    }
    
    public function edit(Request $request, FormAction $case, $id = 0)
    {
        // UseCase内でリレーションがロードされた $project を取得
        list($project, $relatedBizs) = $case($id);

        $defaultPrefCd = null;
        $prefectures = config('prefs') ?: []; // nullなら空配列にする

        // 1. 現場住所コード(city_cd)があれば、その先頭2桁
        if ($project->city_cd) {
            $defaultPrefCd = substr($project->city_cd, 0, 2);
        } 
        // 2. なければ「機関所在地」からコードを特定
        elseif ($project->organization_address && !empty($prefectures)) {
            // 住所文字列（群馬県など）からコード（10など）を逆引き
            // array_search は見つからない場合に false を返すので注意
            $foundCd = array_search($project->organization_address, $prefectures);
            $defaultPrefCd = $foundCd !== false ? $foundCd : null;
        }

        return Inertia::render('Project/form', [
            'project' => array_merge($project->toArray(), [
                // フロントに渡す際、city_cdが無くても都道府県コードが分かれば入れておく
                // 5桁に満たない2桁（'09'など）が渡されても、Vue側は substring(0,2) で処理できる
                'city_cd' => $project->city_cd ?: $defaultPrefCd 
            ]),
            'relatedBizs' => $relatedBizs,
            'prefectures' => $prefectures, // フロントにも渡す
        ]);
    }
    
    public function update(Request $request, $id, UpdateAction $case)
    {
        // バリデーション
        $data = $request->validate([
            'status'          => 'required|integer|between:0,5',
            'expected_amount' => 'nullable|numeric',
            'status_memo'     => 'nullable|string',
            'is_target'       => 'nullable|integer', // ここを追加
        ]);

        // UseCaseの実行
        $case($id, $data);

        // 詳細画面へ戻る（Inertiaが最新の props を再送します）
        return redirect()->back();
    }

    public function matching(Request $request, $project_id, MatchingAction $case, $biz_id = null)
    {
        // フロントから送られてきたデータに biz_id をマージする
        // $biz_id がURLパラメータにある場合はそれを使用し、なければ $request から取る
        $data = $request->all();
        if ($biz_id) {
            $data['biz_id'] = $biz_id;
        }

        $case($project_id, $data);

        return redirect()->back();
    }

    public function unmatching($project_id, $biz_id, UnmatchingAction $case)
    {
        $case($project_id, $biz_id);
        return redirect()->back();
    }
}
