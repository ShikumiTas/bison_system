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
            // 案件への外部キー
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            // 企業への外部キー
            $table->foreignId('biz_id')->constrained()->onDelete('cascade');
            
            // 追加情報：役割（1次下請など）と進捗ステータス
            $table->string('role')->nullable()->comment('役割');
            $table->string('status')->default('requesting')->comment('進捗状態');
            
            $table->timestamps();
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
