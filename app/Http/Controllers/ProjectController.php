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

        return Inertia::render('Project/form', [
            'project'     => $project,
            'relatedBizs' => $relatedBizs, // Eloquentが取得した紐付け企業リスト
        ]);
    }
    
    public function update(Request $request, $id, UpdateAction $case)
    {
        // バリデーション
        $data = $request->validate([
            'status'          => 'required|integer|between:0,5',
            'expected_amount' => 'nullable|numeric',
            'status_memo'     => 'nullable|string',
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
