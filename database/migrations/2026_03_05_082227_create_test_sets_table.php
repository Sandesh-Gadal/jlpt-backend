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
        Schema::create('test_sets', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('jlpt_level_id')->constrained('jlpt_levels');
            $table->string('title', 255);
            $table->enum('test_type', ['practice', 'mock_exam', 'assigned']);
            $table->integer('time_limit_seconds')->default(3600);
            $table->integer('passing_score_percent')->default(60);
            $table->integer('xp_reward_pass')->default(50);
            $table->integer('xp_reward_fail')->default(10);
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_sets');
    }
};
