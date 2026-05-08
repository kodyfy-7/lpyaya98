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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('transaction_id')->nullable();
            $table->uuid('area_id');

            $table->uuid('paid_by_id')->nullable();
            $table->uuid('created_by_id')->nullable();

            $table->decimal('amount', 12, 2);

            $table->unsignedTinyInteger('month');
            $table->year('year');

            $table->timestamp('paid_at')->nullable();

            $table->string('status')->default('pending');

            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('transaction_id')
                ->references('id')
                ->on('transactions')
                ->nullOnDelete();

            $table->foreign('area_id')
                ->references('id')
                ->on('areas')
                ->cascadeOnDelete();

            $table->foreign('paid_by_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('created_by_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
