<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'course_id',
        'title',
        'content_json',
        'lesson_type',
        'video_url',
        'audio_url',
        'estimated_minutes',
        'xp_reward',
        'sort_order',
        'is_published',
    ];

    protected $casts = [
        'content_json'      => 'array',
        'is_published'      => 'boolean',
        'estimated_minutes' => 'integer',
        'xp_reward'         => 'integer',
        'sort_order'        => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function completions()
    {
        return $this->hasMany(LessonCompletion::class);
    }
}
