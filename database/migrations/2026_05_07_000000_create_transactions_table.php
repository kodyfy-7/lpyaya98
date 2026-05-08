<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users', 'id');
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('pending'); // pending, success, failed
            $table->string('payment_method')->default('flutterwave');
            $table->string('reference')->unique();
            $table->string('currency')->default('NGN');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable();
            $table->softDeletes('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
