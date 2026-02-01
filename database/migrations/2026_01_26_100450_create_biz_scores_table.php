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
        Schema::create('biz_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('biz_id')->constrained('bizs')->onDelete('cascade');
            $table->string('work_category')->comment('工種名（土木、建築等）'); 
            $table->string('permit_type')->nullable()->comment('許可区分（般・特）'); 
            $table->integer('p_score')->nullable()->comment('総合評定値(P)');
            $table->bigInteger('avg_sales')->nullable()->comment('完成工事高平均');
            $table->json('details')->nullable()->comment('技術者詳細・評点Z等の詳細データ');
            $table->timestamps();

            $table->index(['work_category', 'p_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biz_scores');
    }
};