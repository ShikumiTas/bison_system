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
            $table->integer('y_score')->nullable();
            $table->bigInteger('total_sales')->nullable();
            $table->double('equity_ratio')->nullable();
            $table->string('employment_insurance')->nullable();
            $table->timestamps();
        });    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biz_financials');
    }
};
