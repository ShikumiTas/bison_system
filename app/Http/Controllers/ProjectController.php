<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

use App\UseCases\Project\IndexAction;
use App\UseCases\Project\FormAction;
use App\UseCases\Project\MatchingAction;
use App\UseCases\Project\UnmatchingAction;

class ProjectController extends Controller
{
    public function index(Request $request, IndexAction $case)
    {
        $post = $request->all();
        
        list($projects, ) = $case($post);

        return Inertia::render('Project/index', [
            'projects' => $projects,

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
    
    public function matching(Request $request, $project_id, MatchingAction $case)
    {
        $case($project_id, $request->all());

        return redirect()->back();
    }

    public function unmatching($project_id, $biz_id, UnmatchingAction $case)
    {
        $case($project_id, $biz_id);
        return redirect()->back();
    }
}
