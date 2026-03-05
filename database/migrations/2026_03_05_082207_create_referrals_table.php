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
        Schema::create('referrals', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('referrer_tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('referred_tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('referral_code', 20);
            $table->enum('status', ['pending', 'converted', 'rewarded'])->default('pending');
            $table->timestamp('converted_at')->nullable();
            $table->timestamp('reward_granted_at')->nullable();
            $table->enum('reward_type', ['free_days', 'discount', 'xp_bonus'])->nullable();
            $table->integer('reward_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
