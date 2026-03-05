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
        Schema::create('feature_gates', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->string('feature_key', 100);
            $table->boolean('is_enabled')->default(false);
            $table->integer('limit_value')->nullable();
            $table->enum('limit_type', ['count', 'days', 'percentage'])->nullable();
            $table->string('upgrade_prompt_key', 100)->nullable();
            $table->timestamps();
            $table->unique(['plan_id', 'feature_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_gates');
    }
};
