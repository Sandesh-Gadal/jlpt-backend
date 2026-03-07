<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\ContentAccessRule;
use App\Models\Course;
use App\Models\LessonCompletion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    // ── GET /api/v1/courses ────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $user     = $request->user();
        $planType = $user->tenant->plan->plan_type ?? 'free';

        $courses = Course::with(['jlptLevel', 'publishedLessons'])
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->get();

        $data = $courses->map(function ($course) use ($planType, $user) {

            $rule      = ContentAccessRule::where('content_type', 'course')
                ->where('content_id', $course->id)
                ->first();

            $hasAccess    = !$rule || $rule->isAccessibleBy($planType);
            $lessonCount  = $course->publishedLessons->count();
            $previewCount = $rule?->preview_lesson_count ?? 0;

            // Count completed lessons for this user
            $completedCount = LessonCompletion::where('user_id', $user->id)
                ->whereIn('lesson_id', $course->publishedLessons->pluck('id'))
                ->count();

            return [
                'id'                  => $course->id,
                'title'               => $course->title,
                'description'         => $course->description,
                'category'            => $course->category,
                'thumbnail_url'       => $course->thumbnail_url,
                'estimated_minutes'   => $course->estimated_minutes,
                'lesson_count'        => $lessonCount,
                'completed_lessons'   => $completedCount,
                'progress_percent'    => $lessonCount > 0
                    ? round(($completedCount / $lessonCount) * 100)
                    : 0,
                'jlpt_level'          => [
                    'code'    => $course->jlptLevel->code,
                    'name_en' => $course->jlptLevel->name_en,
                ],
                // ── Lock info ──────────────────────────────
                'is_locked'           => !$hasAccess,
                'preview_lessons'     => $hasAccess ? null : $previewCount,
                'required_plan'       => $hasAccess ? null : $rule?->min_plan_type,
                'upgrade_prompt'      => $hasAccess ? null : 'upgrade_to_' . $rule?->min_plan_type,
            ];
        });

        return response()->json([
            'courses'      => $data,
            'total'        => $data->count(),
            'your_plan'    => $planType,
        ]);
    }

    // ── GET /api/v1/courses/{id} ───────────────────────────
    public function show(Request $request, string $id): JsonResponse
    {
        $user     = $request->user();
        $planType = $user->tenant->plan->plan_type ?? 'free';

        $course = Course::with(['jlptLevel', 'publishedLessons'])
            ->where('is_published', true)
            ->findOrFail($id);

        // Course level access check
        $courseRule = ContentAccessRule::where('content_type', 'course')
            ->where('content_id', $course->id)
            ->first();

        $courseAccess = !$courseRule || $courseRule->isAccessibleBy($planType);
        $previewCount = $courseRule?->preview_lesson_count ?? 0;

        // Build lessons with individual lock status
        $lessons = $course->publishedLessons->map(function ($lesson, $index) use (
            $planType, $courseAccess, $previewCount, $user
        ) {
            // Check individual lesson rule
            $lessonRule   = ContentAccessRule::where('content_type', 'lesson')
                ->where('content_id', $lesson->id)
                ->first();

            $lessonAccess = $lessonRule
                ? $lessonRule->isAccessibleBy($planType)
                : $courseAccess;

            // Preview override — free users unlock first N lessons
            if (!$lessonAccess && $index < $previewCount) {
                $lessonAccess = true;
            }

            // Check if completed
            $completed = LessonCompletion::where('user_id', $user->id)
                ->where('lesson_id', $lesson->id)
                ->exists();

            return [
                'id'                => $lesson->id,
                'title'             => $lesson->title,
                'lesson_type'       => $lesson->lesson_type,
                'estimated_minutes' => $lesson->estimated_minutes,
                'xp_reward'         => $lesson->xp_reward,
                'sort_order'        => $lesson->sort_order,
                // ── Lock info ──────────────────────────────
                'is_locked'         => !$lessonAccess,
                'is_completed'      => $completed,
                'required_plan'     => !$lessonAccess
                    ? ($lessonRule?->min_plan_type ?? 'individual')
                    : null,
                'upgrade_prompt'    => !$lessonAccess
                    ? 'upgrade_to_' . ($lessonRule?->min_plan_type ?? 'individual')
                    : null,
            ];
        });

        return response()->json([
            'course'  => [
                'id'                => $course->id,
                'title'             => $course->title,
                'description'       => $course->description,
                'category'          => $course->category,
                'thumbnail_url'     => $course->thumbnail_url,
                'estimated_minutes' => $course->estimated_minutes,
                'jlpt_level'        => [
                    'code'    => $course->jlptLevel->code,
                    'name_en' => $course->jlptLevel->name_en,
                ],
                'is_locked'         => !$courseAccess,
                'required_plan'     => !$courseAccess ? $courseRule?->min_plan_type : null,
            ],
            'lessons'       => $lessons,
            'total_lessons' => $lessons->count(),
            'free_lessons'  => $lessons->where('is_locked', false)->count(),
            'locked_lessons'=> $lessons->where('is_locked', true)->count(),
            'your_plan'     => $planType,
        ]);
    }
}