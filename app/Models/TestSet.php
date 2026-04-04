<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestSet extends Model
{
    use HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'jlpt_level_id',
        'title',
        'description',
        'test_type',
        'time_limit_seconds',
        'passing_score_percent',
        'xp_reward_pass',
        'xp_reward_fail',
        'is_published',
    ];

    protected $casts = [
        'is_published'              => 'boolean',
        'time_limit_seconds'       => 'integer',
        'passing_score_percent'    => 'integer',
        'xp_reward_pass'            => 'integer',
        'xp_reward_fail'            => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────

    /**
     * Get the JLPT level that owns this test set.
     */
    public function jlptLevel(): BelongsTo
    {
        return $this->belongsTo(JlptLevel::class, 'jlpt_level_id');
    }

    /**
     * Alias for jlptLevel for compatibility with controllers.
     */
    public function level(): BelongsTo
    {
        return $this->belongsTo(JlptLevel::class, 'jlpt_level_id');
    }

    /**
     * Get all attempts for this test set.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class, 'test_set_id');
    }

    /**
     * Get all question banks associated with this test set.
     */
    public function questionBanks(): HasMany
    {
        return $this->hasMany(QuestionBank::class, 'test_set_id');
    }
}

