<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attempt_answers', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('attempt_id')->constrained('test_attempts')->cascadeOnDelete();
            $table->foreignUuid('question_id')->constrained('questions');
            $table->string('selected_answer', 10)->nullable();
            $table->boolean('is_correct')->nullable();
            $table->boolean('is_flagged')->default(false);
            $table->integer('time_spent_seconds')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attempt_answers');
    }
};
