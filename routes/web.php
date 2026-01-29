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
    
    // 案件管理
    Route::prefix('project')->name('project.')->group(function () {
        // 案件一覧表示
        Route::get('/index', [ProjectController::class, 'index'])->name('index');
        // 案件情報表示
        Route::get('/edit/{id}', [ProjectController::class, 'edit'])->name('edit');
    });

    // 経審・企業管理
    Route::prefix('biz')->name('biz.')->group(function () {
        // 企業情報一覧表示
        Route::get('/index', [BizController::class, 'index'])->name('index');
        // 企業情報表示
        Route::get('/edit/{id}', [BizController::class, 'edit'])->name('edit');

    });
});

require __DIR__.'/settings.php';
