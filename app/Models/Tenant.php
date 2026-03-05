<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'tenant_type',
        'plan_id',
        'billing_email',
        'max_seats',
        'status',
        'referral_code',
        'referred_by_tenant_id',
    ];

    protected $casts = [
        'max_seats' => 'integer',
    ];

    // Auto-generate referral code when creating
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($tenant) {
            if (empty($tenant->referral_code)) {
                $tenant->referral_code = strtoupper(Str::random(8));
            }
        });
    }

    // ── Relationships ──────────────────────────────────────
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}