<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'jlpt_level_id',
        'title',
        'description',
        'thumbnail_url',
        'category',
        'estimated_minutes',
        'sort_order',
        'is_published',
    ];

    protected $casts = [
        'is_published'      => 'boolean',
        'estimated_minutes' => 'integer',
        'sort_order'        => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────
    public function jlptLevel()
    {
        return $this->belongsTo(JlptLevel::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }

    // ── Helpers ────────────────────────────────────────────
    public function publishedLessons()
    {
        return $this->hasMany(Lesson::class)
                    ->where('is_published', true)
                    ->orderBy('sort_order');
    }
}