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
        Schema::create('plans', function (Blueprint $table) {
        $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
        $table->string('name', 100);
        $table->enum('plan_type', ['free', 'individual', 'team', 'institution']);
        $table->enum('billing_cycle', ['free', 'monthly', 'annual']);
        $table->integer('price_usd_cents')->default(0);
        $table->integer('max_seats')->default(1);
        $table->jsonb('features')->default('{}');
        $table->boolean('is_free_forever')->default(false);
        $table->boolean('is_active')->default(true);
        $table->string('stripe_price_id', 255)->nullable();
        $table->integer('display_order')->default(0);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
