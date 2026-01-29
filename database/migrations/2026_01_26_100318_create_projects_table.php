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

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_external_id')->unique(); // CSVの案件ID
            $table->string('title');
            $table->string('bidding_type')->nullable();
            $table->text('url')->nullable();
            $table->string('organization')->nullable();
            $table->date('bid_date')->nullable();
            $table->bigInteger('winning_price')->nullable();
            $table->string('winner_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
