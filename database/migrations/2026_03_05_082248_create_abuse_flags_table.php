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
        Schema::create('abuse_flags', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->enum('target_type', ['user', 'tenant', 'content']);
            $table->uuid('target_id');
            $table->enum('flag_type', ['spam_signups', 'payment_fraud', 'content_abuse', 'bot_activity']);
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->jsonb('details')->default('{}');
            $table->enum('status', ['open', 'reviewed', 'dismissed', 'actioned'])->default('open');
            $table->foreignUuid('reviewed_by')->nullable()->constrained('platform_admins')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abuse_flags');
    }
};
