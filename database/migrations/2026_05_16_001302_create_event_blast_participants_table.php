<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_blast_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('event_blast_id');
            $table->uuid('event_participant_id');
            $table->string('email');
            $table->enum('status', ['pending', 'sent', 'failed', 'skipped'])
                  ->default('pending');
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->foreign('event_blast_id')
                  ->references('id')
                  ->on('event_blasts')
                  ->cascadeOnDelete();

            $table->foreign('event_participant_id')
                  ->references('id')
                  ->on('event_participants')
                  ->cascadeOnDelete();

            $table->index('event_blast_id');
            $table->index('event_participant_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_blast_participants');
    }
};