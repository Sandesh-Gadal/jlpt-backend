<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestAttempt extends Model
{
    use HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'test_set_id',
        'user_id',
        'status',
        'score_percent',
        'passed',
        'xp_awarded',
        'started_at',
        'submitted_at',
    ];

    protected $casts = [
        'status'        => 'string',
        'score_percent' => 'decimal:2',
        'passed'        => 'boolean',
        'xp_awarded'    => 'integer',
        'started_at'    => 'datetime',
        'submitted_at'  => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────

    /**
     * Get the test set that owns this attempt.
     */
    public function testSet(): BelongsTo
    {
        return $this->belongsTo(TestSet::class, 'test_set_id');
    }

    /**
     * Get the user that owns this attempt.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all answers for this attempt.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class, 'attempt_id');
    }
}

