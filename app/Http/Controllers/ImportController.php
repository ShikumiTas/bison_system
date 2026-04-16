<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\UseCases\Import\CheckAction;

class ImportController extends Controller
{
    public function index() { return Inertia::render('Import'); }

    public function store(Request $request, CheckAction $checkAction)
    {
        $mode = $request->input('mode', 'preview');

        if ($mode === 'preview') {
            // ★1. バリデーションのキーを file に変更し、タイプに応じて拡張子チェックを変える
            $fileRule = $request->type === 'qualification' ? 'required|file|mimes:pdf' : 'required|file';
            
            $request->validate([
                'file' => $fileRule, 
                'type' => 'required'
            ]);
            
            // ★2. ここも $request->file('file') に変更
            $path = $request->file('file')->store('imports', 'local');
            
            $results = $checkAction->execute($path, $request->type);

            return back()->with([
                'import_results' => $results,
                'temp_file_path' => $path,
                'import_type'    => $request->type,
                'message'        => '整合性チェック完了'
            ]);
        }

        // 確定実行
        $tempPath = $request->input('temp_path');
        
        // ★3. 資格情報(PDF)用の分岐を追加
        if ($request->type === 'qualification') {
            // コマンドではなく、直接Jobをキューに投入する
            dispatch(new \App\Jobs\AnalyzeQualificationPdfJob(
                Storage::disk('local')->path($tempPath)
            ));
        } else {
            // CSV系は既存通りコマンドをキューに
            $command = ($request->type === 'project') ? 'import:projects' : 'import:bizs';
            Artisan::queue($command, [
                'path' => Storage::disk('local')->path($tempPath)
            ]);
        }

        return back()->with('message', 'バックグラウンドで処理を開始しました。');
    }
}