<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Prevents duplicate payments at the DB level — the last line of defence
            // against race conditions that updateOrCreate alone cannot guarantee.
            $table->unique(['month', 'year', 'area_id'], 'payments_period_area_unique');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('payments_period_area_unique');
        });
    }
};
