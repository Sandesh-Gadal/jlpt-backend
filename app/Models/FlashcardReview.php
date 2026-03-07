<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class FlashcardReview extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id', 'flashcard_id',
        'repetitions', 'easiness', 'interval',
        'next_review_at', 'last_rating',
    ];

    protected $casts = [
        'next_review_at' => 'datetime',
        'easiness'       => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function flashcard()
    {
        return $this->belongsTo(Flashcard::class);
    }
}
