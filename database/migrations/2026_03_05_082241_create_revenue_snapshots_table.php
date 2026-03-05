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
        Schema::create('revenue_snapshots', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->date('snapshot_date')->unique();
            $table->integer('mrr_cents')->default(0);
            $table->integer('arr_cents')->default(0);
            $table->integer('new_subscriptions')->default(0);
            $table->integer('churned_subscriptions')->default(0);
            $table->integer('upgrades')->default(0);
            $table->integer('downgrades')->default(0);
            $table->integer('free_users_total')->default(0);
            $table->integer('paid_users_total')->default(0);
            $table->jsonb('plan_breakdown')->default('{}');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenue_snapshots');
    }
};
