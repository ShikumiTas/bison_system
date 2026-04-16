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
        Schema::create('qualifications', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('authority_name')->comment('発行元の機関名');
            $blueprint->string('license_name')->comment('資格の正式名称');
            $blueprint->string('trader_code')->nullable()->comment('業者コード・登録番号');
            $blueprint->date('valid_from')->nullable()->comment('有効期間の開始日');
            $blueprint->date('valid_to')->nullable()->comment('有効期間の終了日');
            
            // ★ここがポイント：JSON型のカラム
            $blueprint->json('business_items')->comment('業種・スコアの詳細データ');
            
            $blueprint->string('pdf_path')->nullable()->comment('PDFファイルの保存パス');
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qualifications');
    }
};