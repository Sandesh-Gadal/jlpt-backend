<?php
// database/migrations/xxxx_create_flashcards_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('flashcards', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->foreignUuid('jlpt_level_id')->constrained('jlpt_levels');
            $table->enum('category', ['vocabulary', 'grammar', 'kanji', 'phrase']);
            $table->string('front_text', 100);        // Japanese word/kanji
            $table->string('front_reading', 100)->nullable(); // furigana
            $table->text('back_text');                // English meaning
            $table->text('example_jp')->nullable();
            $table->text('example_en')->nullable();
            $table->string('audio_url', 500)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('flashcards'); }
};