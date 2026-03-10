<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionBank extends Model
{
    use HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'jlpt_level_id',
        'test_set_id',
        'name',
        'section_type',
        'question_count',
    ];

    protected $casts = [
        'question_count' => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────

    /**
     * Get the JLPT level that owns this question bank.
     */
    public function jlptLevel(): BelongsTo
    {
        return $this->belongsTo(JlptLevel::class, 'jlpt_level_id');
    }

    /**
     * Get the test set that owns this question bank.
     */
    public function testSet(): BelongsTo
    {
        return $this->belongsTo(TestSet::class, 'test_set_id');
    }

    /**
     * Get all questions in this bank.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'question_bank_id');
    }
}

