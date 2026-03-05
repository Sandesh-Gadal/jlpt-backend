<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'plan_type',
        'billing_cycle',
        'price_usd_cents',
        'max_seats',
        'features',
        'is_free_forever',
        'is_active',
        'stripe_price_id',
        'display_order',
    ];

    protected $casts = [
        'features'        => 'array',
        'is_free_forever' => 'boolean',
        'is_active'       => 'boolean',
        'price_usd_cents' => 'integer',
        'max_seats'       => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────
    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    public function featureGates()
    {
        return $this->hasMany(FeatureGate::class);
    }

    // ── Helpers ────────────────────────────────────────────
    public function isFree(): bool
    {
        return $this->plan_type === 'free';
    }

    public function priceInDollars(): float
    {
        return $this->price_usd_cents / 100;
    }
}