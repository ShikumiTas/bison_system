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
            // 都道府県(2)+区分(1)+番号(8) または 手動用(99+9)
            $table->string('permit_id')->unique()->index(); 
            $table->string('company_name');
            $table->string('permit_number_raw')->nullable()->comment('CSVの元の表記');
            $table->string('zip_code', 8)->nullable();
            $table->string('address')->nullable();
            $table->string('phone_number')->nullable();
            $table->bigInteger('capital')->nullable();
            $table->date('review_base_date')->nullable();
            $table->boolean('is_manual')->default(false); // 手動登録判定
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
