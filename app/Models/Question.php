<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    use HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'question_bank_id',
        'question_type',
        'prompt',
        'options',
        'correct_answer',
        'explanation',
        'difficulty',
        'audio_url',
        'is_published',
    ];

    protected $casts = [
        'options'       => 'array',
        'is_published' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────────────

    /**
     * Get the question bank that owns this question.
     */
    public function questionBank(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class, 'question_bank_id');
    }
}

