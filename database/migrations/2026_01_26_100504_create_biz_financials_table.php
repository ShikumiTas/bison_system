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
        Schema::create('biz_financials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('biz_id')->constrained('bizs')->onDelete('cascade');
            
            // --- 経営状況評点（Y点）関連 ---
            // CSV項目「評点（Ｙ）」に対応
            $table->integer('y_score')->nullable()->comment('経営状況評点(Y)');

            // --- 財務三表レベルの重要指標 ---
            // CSVの「純利益」「自己資本」等の生データから抽出
            $table->bigInteger('total_net_sales')->nullable()->comment('売上高');
            $table->bigInteger('gross_profit')->nullable()->comment('売上総利益');
            $table->bigInteger('net_worth')->nullable()->comment('自己資本（純資産）');
            
            // 指標系（計算済みデータ）
            $table->double('equity_ratio')->nullable()->comment('自己資本比率(%)');
            $table->double('current_ratio')->nullable()->comment('流動比率(%)：短期的な支払い能力');
            $table->double('operating_profit_ratio')->nullable()->comment('営業利益率(%)');

            // --- 社会性評点（W点）およびその他フラグ ---
            // W点は「会社としてちゃんとしているか」の指標
            $table->integer('w_score')->nullable()->comment('社会性等評点(W)');
            $table->boolean('has_iso')->default(false)->comment('ISO取得有無');
            $table->boolean('has_disaster_agreement')->default(false)->comment('防災協定締結の有無');

            // --- JSON管理エリア ---
            // 「主要ではないが、たまに確認したい項目」をすべてここに入れる
            // 例：建退共加入、ワークライフバランス、若手育成、snapshotなど
            $table->json('social_details')->nullable()->comment('社会性Wの細目（雇用保険・退職金・若手等）');
            $table->json('raw_snapshot')->nullable()->comment('CSVの全データバックアップ');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biz_financials');
    }
};
