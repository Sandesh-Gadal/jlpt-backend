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
        Schema::create('content_access_rules', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->enum('content_type', ['course', 'lesson', 'test_set', 'question_bank']);
            $table->uuid('content_id');
            $table->enum('min_plan_type', ['free', 'individual', 'team', 'institution']);
            $table->boolean('preview_allowed')->default(false);
            $table->integer('preview_lesson_count')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_access_rules');
    }
};
