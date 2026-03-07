<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Flashcard extends Model
{
    use HasUuids;

    protected $fillable = [
        'course_id', 'jlpt_level_id', 'category',
        'front_text', 'front_reading', 'back_text',
        'example_jp', 'example_en', 'audio_url',
        'sort_order', 'is_published',
    ];

    protected $casts = ['is_published' => 'boolean'];

    public function level()
    {
        return $this->belongsTo(JlptLevel::class, 'jlpt_level_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function reviews()
    {
        return $this->hasMany(FlashcardReview::class);
    }
}
