<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            
            // 外部キー
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('biz_id')->constrained()->onDelete('cascade');
            
            // 追加情報
            $table->string('role')->nullable()->comment('役割: 1次下請, 2次下請など');
            $table->string('status')->default('requesting')->comment('進捗状態: 見積中, 受領済など');
            
            // メモカラムを追加！ (長文になる可能性を考慮して text型)
            $table->text('memo')->nullable()->comment('社内メモ・交渉記録');
            
            $table->timestamps();

            // 同じ案件に同じ企業を重複して登録できないようにユニーク制約をかける（推奨）
            $table->unique(['project_id', 'biz_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};