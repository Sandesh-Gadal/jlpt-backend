<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\ContentAccessRule;
use App\Models\Course;
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

        $data = $courses->map(function ($course) use ($planType) {
            $rule = ContentAccessRule::where('content_type', 'course')
                ->where('content_id', $course->id)
                ->first();

            $hasAccess    = !$rule || $rule->isAccessibleBy($planType);
            $lessonCount  = $course->publishedLessons->count();

            return [
                'id'                => $course->id,
                'title'             => $course->title,
                'description'       => $course->description,
                'category'          => $course->category,
                'thumbnail_url'     => $course->thumbnail_url,
                'estimated_minutes' => $course->estimated_minutes,
                'lesson_count'      => $lessonCount,
                'jlpt_level'        => [
                    'code'    => $course->jlptLevel->code,
                    'name_en' => $course->jlptLevel->name_en,
                ],
                'has_access'        => $hasAccess,
                'preview_lessons'   => $rule?->preview_lesson_count ?? 0,
                'locked'            => !$hasAccess,
                'upgrade_required'  => !$hasAccess ? 'individual' : null,
            ];
        });

        return response()->json([
            'courses' => $data,
            'total'   => $data->count(),
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

        // Check course access
        $rule      = ContentAccessRule::where('content_type', 'course')
            ->where('content_id', $course->id)
            ->first();
        $hasAccess = !$rule || $rule->isAccessibleBy($planType);

        // Build lesson list with access flags
        $lessons = $course->publishedLessons->map(function ($lesson, $index) use ($planType, $rule, $hasAccess) {

            // Check individual lesson access rule
            $lessonRule = ContentAccessRule::where('content_type', 'lesson')
                ->where('content_id', $lesson->id)
                ->first();

            $lessonAccess = $lessonRule
                ? $lessonRule->isAccessibleBy($planType)
                : $hasAccess;

            // Preview: free users can see first N lessons
            $previewCount = $rule?->preview_lesson_count ?? 0;
            if (!$lessonAccess && $rule?->preview_allowed && $index < $previewCount) {
                $lessonAccess = true;
            }

            return [
                'id'                => $lesson->id,
                'title'             => $lesson->title,
                'lesson_type'       => $lesson->lesson_type,
                'estimated_minutes' => $lesson->estimated_minutes,
                'xp_reward'         => $lesson->xp_reward,
                'sort_order'        => $lesson->sort_order,
                'is_locked'         => !$lessonAccess,
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
                'has_access'        => $hasAccess,
            ],
            'lessons' => $lessons,
            'total_lessons' => $lessons->count(),
        ]);
    }
}