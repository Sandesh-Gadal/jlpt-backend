<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ContentAccessRule extends Model
{
    use HasUuids;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'content_type',
        'content_id',
        'min_plan_type',
        'preview_allowed',
        'preview_lesson_count',
    ];

    protected $casts = [
        'preview_allowed'     => 'boolean',
        'preview_lesson_count'=> 'integer',
    ];

    // Plan hierarchy for comparison
    public static array $planHierarchy = [
        'free'        => 0,
        'individual'  => 1,
        'team'        => 2,
        'institution' => 3,
    ];

    // Check if a plan type can access this content
    public function isAccessibleBy(string $planType): bool
    {
        $userLevel    = self::$planHierarchy[$planType]    ?? 0;
        $requiredLevel= self::$planHierarchy[$this->min_plan_type] ?? 0;

        return $userLevel >= $requiredLevel;
    }
}