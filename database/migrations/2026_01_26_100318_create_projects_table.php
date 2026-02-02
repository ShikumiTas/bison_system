<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_external_id')->unique()->comment('案件ID');
            $table->string('bidding_type')->nullable()->comment('入札形式');
            $table->string('title')->comment('案件名');
            $table->text('url')->nullable()->comment('案件概要URL');
            
            // 発注者情報
            $table->string('organization')->nullable()->comment('機関名');
            $table->string('organization_address')->nullable()->comment('機関所在地');
            $table->string('delivery_location')->nullable()->comment('履行/納品場所');
            
            // スケジュール
            $table->date('notice_date')->nullable()->comment('案件公示日');
            $table->date('bid_date')->nullable()->comment('入札日');
            $table->date('briefing_date')->nullable()->comment('説明会日');
            $table->date('document_deadline')->nullable()->comment('資料等提出日');

            // 案件内容
            $table->text('bidding_qualifications')->nullable()->comment('入札資格');
            $table->text('industry')->nullable()->comment('業種');
            $table->text('description')->nullable()->comment('案件概要');
            $table->text('notes')->nullable()->comment('案件備考');

            // ★追加：案件ステータスとメモ
            // 0:準備中(企業0), 1:進行中(企業1~), 2:完工, 9:中止 などを想定
            $table->tinyInteger('status')->default(0)->index()->comment('案件ステータス');
            $table->text('status_memo')->nullable()->comment('ステータスに関する補足メモ');
            $table->bigInteger('expected_amount')->nullable()->comment('詳細想定金額');
            
            // 落札者情報
            $table->string('winner_name')->nullable()->comment('落札会社名');
            $table->string('winner_address')->nullable()->comment('落札会社住所');
            $table->bigInteger('winning_price')->nullable()->comment('落札価格');
            
            // 企業(bizs)との紐付け用
            $table->string('biz_permit_id')->nullable()->index()
                  ->comment('bizs.permit_idとの紐付け用');

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('projects');
    }
};