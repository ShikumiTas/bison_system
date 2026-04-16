<?php

namespace App\Http\Controllers;

use App\Models\Qualification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

use App\UseCases\Qualification\IndexAction;

class QualificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, IndexAction $case)
    {
        $post = $request->all();

        list($qualifications, ) = $case($post);

        return Inertia::render('Qualification/index', [
            'qualifications' => $qualifications,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Qualification $qualification)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Qualification $qualification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Qualification $qualification)
    {
        //
    }

/**
     * 指定された資格情報を削除
     */
    public function destroy(Qualification $qualification)
    {
        try {
            // 1. ファイルが存在する場合は削除
            if ($qualification->pdf_path) {
                $diskName = config('filesystems.qualification_disk', 'local_secure');
                $disk = \Illuminate\Support\Facades\Storage::disk($diskName);

                if ($disk->exists($qualification->pdf_path)) {
                    $disk->delete($qualification->pdf_path);
                    \Illuminate\Support\Facades\Log::info("PDFファイルを削除しました: {$qualification->pdf_path}");
                }
            }

            // 2. DBのレコードを削除
            $qualification->delete();

            // 3. 一覧画面へリダイレクト（Inertiaのフラッシュメッセージ付き）
            return redirect()->route('qualification.index')
                ->with('message', '資格情報を削除しました。');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("資格削除に失敗しました: " . $e->getMessage());
            
            return redirect()->back()
                ->with('error', '削除処理中にエラーが発生しました。');
        }
    }
    
    /**
     * 資格PDFをブラウザで表示する
     */
    public function viewPdf(Qualification $qualification)
    {
        // 1. 設定ファイル等から使用するディスク名を取得（将来のS3移行を楽にするため）
        // とりあえずは 'local_secure' をデフォルトにする
        $diskName = config('filesystems.qualification_disk', 'local_secure');
        $disk = Storage::disk($diskName);

        // 2. ファイルの存在確認
        if (!$qualification->pdf_path || !$disk->exists($qualification->pdf_path)) {
            abort(404, '証明書PDFファイルが見つかりません。');
        }

        // 3. ファイルデータの取得
        $file = $disk->get($qualification->pdf_path);
        $mimeType = $disk->mimeType($qualification->pdf_path) ?? 'application/pdf';

        // 4. レスポンスを返す
        // 'inline' を指定することで、ダウンロードさせずにブラウザのビューアを開かせる
        return response($file, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($qualification->pdf_path) . '"',
            'Cache-Control' => 'private, max-age=31536000',
        ]);
    }
}
