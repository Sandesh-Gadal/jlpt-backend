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
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('name', 255);
            $table->enum('tenant_type', ['individual', 'team', 'institution']);
            $table->foreignUuid('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->string('billing_email', 255)->nullable();
            $table->integer('max_seats')->default(1);
            $table->enum('status', ['active', 'suspended', 'cancelled', 'free'])->default('free');
            $table->string('referral_code', 20)->unique();
            $table->uuid('referred_by_tenant_id')->nullable();
            $table->timestamps();
            });

            // Add self-referencing FK separately AFTER table is created
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreign('referred_by_tenant_id')
                ->references('id')
                ->on('tenants')
                ->nullOnDelete();
     });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['referred_by_tenant_id']);
        });
        Schema::dropIfExists('tenants');    
        }
};
