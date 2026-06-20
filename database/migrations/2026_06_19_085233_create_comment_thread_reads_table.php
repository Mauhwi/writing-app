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
        Schema::create('comment_thread_reads', function (Blueprint $table) {
            $table->id();

            $table->foreignId('thread_id')
                ->constrained('comment_threads')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('last_seen_message_id')
                ->nullable()
                ->constrained('comment_messages')
                ->nullOnDelete();

            $table->timestamps();

            $table->unique(['thread_id', 'user_id']);
            $table->index(['user_id', 'thread_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_thread_reads');
    }
};
