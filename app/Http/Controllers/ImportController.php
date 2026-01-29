<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;

class ImportController extends Controller
{
    public function index()
    {
        // ここで 'flash' => [] を定義してしまうと、
        // Middleware(HandleInertiaRequests) で設定した共有データが消えるので、シンプルに render だけにする
        return Inertia::render('Import');
    }

    public function store(Request $request)
    {
        // バリデーションを有効化
        $request->validate([
            'type' => 'required|in:projects,bizs',
            'csv_file' => 'required|file', // mimes:csv は環境によって txt と判定されることがあるので一旦緩めてもOK
        ]);

        // 1. ファイルを一時保存 (storage/app/private/imports 等に保存されます)
        $path = $request->file('csv_file')->store('imports');
        
        // storage_path('app/' . $path) が一般的ですが、Laravel11等の最新構成なら
        // storage_path('app/private/' . $path) になる場合があるため確認が必要です
        $fullPath = storage_path('app/' . $path);

        // 2. コマンドの選択
        $command = $request->type === 'projects' ? 'import:projects' : 'import:bizs';

        // 3. バックグラウンドで実行
        Artisan::queue($command, [
            'path' => $fullPath
        ]);

        return back()->with('message', 'アップロードが完了しました。バックグラウンドで処理を開始します。');
    }
}