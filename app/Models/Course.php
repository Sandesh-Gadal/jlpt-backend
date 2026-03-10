<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Course extends Model
{
    use HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'jlpt_level_id',
        'title',
        'slug',
        'description',
        'thumbnail_url',
        'category',
        'estimated_minutes',
        'sort_order',
        'is_published',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($course) {
            if (empty($course->slug)) {
                $course->slug = static::generateUniqueSlug($course->title);
            }
        });

        static::updating(function ($course) {
            if ($course->isDirty('title') && empty($course->slug)) {
                $course->slug = static::generateUniqueSlug($course->title);
            }
        });
    }

    /**
     * Generate a unique slug from the title.
     */
    public static function generateUniqueSlug(string $title, ?string $excludeId = null): string
    {
        $slug = Str::slug($title);
        $query = static::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $count = $query->count();

        if ($count > 0) {
            $slug = "{$slug}-" . ($count + 1);
        }

        return $slug;
    }

    /**
     * Find a course by UUID or slug.
     */
    public static function findByIdOrSlug(string $idOrSlug): ?self
    {
        // Only attempt UUID lookup if the string is a valid UUID
        // This prevents PostgreSQL errors when passing a slug to find()
        if (Str::isUuid($idOrSlug)) {
            $course = static::find($idOrSlug);
            if ($course) {
                return $course;
            }
        }

        // Fall back to slug lookup
        return static::where('slug', $idOrSlug)->first();
    }

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