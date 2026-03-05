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
        Schema::create('courses', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('jlpt_level_id')->constrained('jlpt_levels');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('thumbnail_url', 500)->nullable();
            $table->enum('category', ['vocabulary', 'grammar', 'kanji', 'reading', 'listening', 'mixed']);
            $table->integer('estimated_minutes')->default(0);
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
        Schema::dropIfExists('courses');
    }
};
