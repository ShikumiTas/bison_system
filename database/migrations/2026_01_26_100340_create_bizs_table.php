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
        Schema::create('bizs', function (Blueprint $table) {
            $table->id();
            
            // 基本識別情報
            // 都道府県(2)+区分(1)+番号(8) または 手動用(99+9)
            $table->string('permit_id')->unique()->index()->comment('システム独自の許可ID（都道府県2+区分1+番号8）'); 
            $table->string('company_name')->comment('社名');
            $table->string('representative_name')->nullable()->comment('代表者名');
            $table->string('permit_number_raw')->nullable()->comment('CSVの元の許可番号表記');
            
            // 連絡先・所在地
            $table->string('zip_code', 8)->nullable()->comment('郵便番号');
            $table->string('address')->nullable()->comment('所在地');
            $table->string('phone_number')->nullable()->comment('電話番号');
            $table->string('city_code')->nullable()->comment('市区町村コード');
            
            // 財務・審査情報
            $table->bigInteger('capital')->nullable()->comment('資本金（円）');
            $table->decimal('sales_ratio', 8, 2)->nullable()->comment('完成工事高／売上高（％）');
            $table->string('admin_section')->nullable()->comment('行政庁記入欄');
            $table->date('review_base_date')->nullable()->comment('審査基準日');
            
            // システム管理用
            $table->boolean('is_manual')->default(false)->comment('手動登録フラグ（trueならユーザーによる追加）');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bizs');
    }
};