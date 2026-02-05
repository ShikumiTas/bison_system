<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

use App\Http\Controllers\BizController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ImportController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::post('/import', [ImportController::class, 'store'])->name('import.store');
    Route::get('/api/biz/search', [BizController::class, 'search'])->name('api.biz.search');

    // 案件管理（ここに紐付け系も全部まとめる）
    Route::prefix('project')->name('project.')->group(function () {
        Route::get('/index', [ProjectController::class, 'index'])->name('index');
        Route::get('/edit/{id}', [ProjectController::class, 'edit'])->name('edit');
        Route::patch('/{id}', [ProjectController::class, 'update'])->name('update');

        // --- 案件に対する企業のマッチング（紐付け）関連 ---
        // prefixの中なので URLは /project/{project}/matching になる
        Route::post('/{project}/matching', [ProjectController::class, 'matching'])->name('matching');
        Route::patch('/{project}/matching/{biz}', [ProjectController::class, 'matching'])->name('matching.update');
        Route::delete('/{project}/matching/{biz}', [ProjectController::class, 'unmatching'])->name('unmatching');
    });

    // 経審・企業管理
    Route::prefix('biz')->name('biz.')->group(function () {
        Route::get('/index', [BizController::class, 'index'])->name('index');
        Route::get('/edit/{id}', [BizController::class, 'edit'])->name('edit');
        Route::delete('/{id}', [BizController::class, 'destroy'])->name('destroy');
    });
});
require __DIR__.'/settings.php';
