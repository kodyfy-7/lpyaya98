<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Replace the old unique constraint — multiple payments now allowed per period
            $table->dropUnique('payments_period_area_unique');

            // Link to the due this payment contributes toward
            $table->uuid('monthly_due_id')->nullable()->after('area_id');
            $table->foreign('monthly_due_id')->references('id')->on('monthly_dues')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['monthly_due_id']);
            $table->dropColumn('monthly_due_id');
            $table->unique(['month', 'year', 'area_id'], 'payments_period_area_unique');
        });
    }
};