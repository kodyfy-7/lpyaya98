<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_dues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('area_id');
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->decimal('due_amount', 12, 2);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('area_id')->references('id')->on('areas')->onDelete('cascade');

            // One due record per period per area
            $table->unique(['month', 'year', 'area_id'], 'monthly_dues_period_area_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_dues');
    }
};
