<?php
// database/migrations/xxxx_create_flashcard_reviews_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('flashcard_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('flashcard_id')->constrained('flashcards')->cascadeOnDelete();

            // SM-2 algorithm fields
            $table->integer('repetitions')->default(0);    // n
            $table->float('easiness')->default(2.5);       // EF (easiness factor)
            $table->integer('interval')->default(1);       // days until next review
            $table->timestamp('next_review_at')->useCurrent();
            $table->integer('last_rating')->nullable();    // 0-5

            $table->timestamps();
            $table->unique(['user_id', 'flashcard_id']);
            $table->index(['user_id', 'next_review_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('flashcard_reviews'); }
};