<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('userId');
            $table->integer('otp');
            $table->timestamp('otpExpiresAt');
            $table->timestamp('expiredAt')->nullable();
            $table->timestamp('createdAt');
            $table->timestamp('updatedAt');
            $table->softDeletes('deletedAt');

            $table->index('userId');
            $table->foreign('userId')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_verifications');
    }
};
