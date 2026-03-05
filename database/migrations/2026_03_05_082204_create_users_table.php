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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('email', 255)->unique();
            $table->string('password_hash');
            $table->string('full_name', 255);
            $table->enum('role', ['owner', 'admin', 'learner', 'super_admin'])->default('learner');
            $table->enum('jlpt_target_level', ['N1', 'N2', 'N3', 'N4', 'N5'])->nullable();
            $table->string('ui_language', 10)->default('en');
            $table->text('avatar_url')->nullable();
            $table->boolean('email_verified')->default(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamp('free_trial_started_at')->nullable();
            $table->timestamp('free_trial_ends_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
