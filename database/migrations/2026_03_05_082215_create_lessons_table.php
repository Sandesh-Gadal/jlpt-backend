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
        Schema::create('lessons', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('title', 255);
            $table->longText('content_json');
            $table->enum('lesson_type', ['text', 'video', 'audio', 'flashcard', 'mixed']);
            $table->string('video_url', 500)->nullable();
            $table->string('audio_url', 500)->nullable();
            $table->integer('estimated_minutes')->default(10);
            $table->integer('xp_reward')->default(10);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
