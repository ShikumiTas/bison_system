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
            $table->string('work_category'); // 土木, 建築等
            $table->string('permit_type')->nullable(); // 般・特
            $table->integer('p_score')->nullable();
            $table->bigInteger('avg_sales')->nullable();
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
