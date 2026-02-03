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
            $request->validate(['csv_file' => 'required|file', 'type' => 'required']);
            $path = $request->file('csv_file')->store('imports', 'local');
            
            // CheckAction側で許可番号空を許容するように修正が必要
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
        $command = ($request->type === 'project') ? 'import:projects' : 'import:bizs';
        
        Artisan::queue($command, [
            'path' => Storage::disk('local')->path($tempPath)
        ]);

        return back()->with('message', 'バックグラウンドで処理を開始しました。');
    }
}