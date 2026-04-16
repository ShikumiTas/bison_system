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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            // 都道府県コード (01〜47) 検索性を高めるため index を付与
            $table->string('pref_cd', 2)->index();
            // 市区町村コード (JISコード等 5桁) 
            $table->string('city_cd', 5)->unique();
            // 市区町村名
            $table->string('name', 64);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};