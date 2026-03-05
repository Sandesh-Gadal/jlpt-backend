<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class FeatureGate extends Model
{
    use HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'plan_id',
        'feature_key',
        'is_enabled',
        'limit_value',
        'limit_type',
        'upgrade_prompt_key',
    ];

    protected $casts = [
        'is_enabled'   => 'boolean',
        'limit_value'  => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    // ── Helpers ────────────────────────────────────────────

    // Check if a feature is enabled for a given plan
    public static function isEnabled(string $planId, string $featureKey): bool
    {
        $gate = static::where('plan_id', $planId)
                      ->where('feature_key', $featureKey)
                      ->first();

        return $gate?->is_enabled ?? false;
    }

    // Get the limit value for a feature (null = unlimited)
    public static function getLimit(string $planId, string $featureKey): ?int
    {
        $gate = static::where('plan_id', $planId)
                      ->where('feature_key', $featureKey)
                      ->first();

        return $gate?->limit_value;
    }

    // Get the upgrade prompt key for a gated feature
    public static function getUpgradePrompt(string $planId, string $featureKey): ?string
    {
        $gate = static::where('plan_id', $planId)
                      ->where('feature_key', $featureKey)
                      ->first();

        return $gate?->upgrade_prompt_key;
    }
}