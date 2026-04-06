<?php

namespace App\Http\Controllers;

use App\Services\XpService;
use App\Models\LessonCompletion;
use App\Models\TestAttempt;
use App\Models\FlashcardReview;
use App\Models\Course;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private XpService $xp) {}

    /**
     * GET /dashboard
     * Returns all data needed by dashboard
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // XP + level + streak
        $xpStats = $this->xp->getDashboardStats($user);

        // Recent lesson completions (last 7 days for sparkline)
        $weeklyActivity = LessonCompletion::where('user_id', $user->id)
            ->where('completed_at', '>=', now()->subDays(6))
            ->selectRaw("DATE(completed_at) as date, COUNT(*) as count, SUM(xp_awarded) as xp")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $sparkline = collect(range(6, 0))->map(function ($daysAgo) use ($weeklyActivity) {
            $date = now()->subDays($daysAgo)->toDateString();

            return [
                'date'  => $date,
                'count' => $weeklyActivity[$date]->count ?? 0,
            ];
        });

        // In-progress courses
        $inProgress = Course::whereHas('lessons.completions', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with([
                'jlptLevel:id,code',
                'lessons' => function ($q) {
                    $q->select('id', 'course_id', 'title', 'sort_order', 'xp_reward')
                        ->orderBy('sort_order');
                },
            ])
            ->take(4)
            ->get()
            ->map(function ($course) use ($user) {
                $total = $course->lessons->count();

                $completedLessonIds = LessonCompletion::where('user_id', $user->id)
                    ->whereIn('lesson_id', $course->lessons->pluck('id'))
                    ->pluck('lesson_id');

                $completed = $completedLessonIds->count();

                $lastCompletedLesson = $course->lessons
                    ->whereIn('id', $completedLessonIds)
                    ->sortByDesc('sort_order')
                    ->first();

                $nextLesson = $course->lessons
                    ->whereNotIn('id', $completedLessonIds)
                    ->sortBy('sort_order')
                    ->first();

                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'category' => $course->category,
                    'estimated_minutes' => $course->estimated_minutes ?? 30,
                    'progress_pct' => $total > 0 ? round(($completed / $total) * 100) : 0,
                    'completed_lessons' => $completed,
                    'total_lessons' => $total,
                    'level' => [
                        'code' => $course->jlptLevel->code ?? 'N5',
                    ],
                    'thumbnail_url' => $course->thumbnail_url ?? null,
                    'last_completed_lesson' => $lastCompletedLesson ? [
                        'id' => $lastCompletedLesson->id,
                        'title' => $lastCompletedLesson->title,
                        'sort_order' => $lastCompletedLesson->sort_order,
                    ] : null,
                    'next_lesson' => $nextLesson ? [
                        'id' => $nextLesson->id,
                        'title' => $nextLesson->title,
                        'sort_order' => $nextLesson->sort_order,
                    ] : null,
                ];
            })
            ->values();

        // Find latest completed lesson for continue card
        $latestCompletion = LessonCompletion::where('user_id', $user->id)
            ->with([
                'lesson.course.jlptLevel:id,code',
                'lesson.course.lessons' => function ($q) {
                    $q->select('id', 'course_id', 'title', 'sort_order', 'xp_reward')
                        ->orderBy('sort_order');
                },
            ])
            ->orderByDesc('completed_at')
            ->first();

        $continueCourse = null;

        if ($latestCompletion && $latestCompletion->lesson && $latestCompletion->lesson->course) {
            $course = $latestCompletion->lesson->course;
            $totalLessons = $course->lessons->count();

            $completedLessonIds = LessonCompletion::where('user_id', $user->id)
                ->whereIn('lesson_id', $course->lessons->pluck('id'))
                ->pluck('lesson_id');

            $completedCount = $completedLessonIds->count();

            $nextLesson = $course->lessons
                ->whereNotIn('id', $completedLessonIds)
                ->sortBy('sort_order')
                ->first();

            $displayLesson = $nextLesson ?: $latestCompletion->lesson;

            $continueCourse = [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'category' => $course->category,
                'estimated_minutes' => $course->estimated_minutes ?? 30,
                'progress_pct' => $totalLessons > 0 ? round(($completedCount / $totalLessons) * 100) : 0,
                'completed_lessons' => $completedCount,
                'total_lessons' => $totalLessons,
                'level' => [
                    'code' => $course->jlptLevel->code ?? 'N5',
                ],
                'thumbnail_url' => $course->thumbnail_url ?? null,
                'current_lesson' => [
                    'id' => $displayLesson->id,
                    'title' => $displayLesson->title,
                    'sort_order' => $displayLesson->sort_order,
                ],
            ];
        }

        // Flashcards due today
        $flashcardsDue = FlashcardReview::where('user_id', $user->id)
            ->where('next_review_at', '<=', now())
            ->count();

        // Recent test results
        $recentTests = TestAttempt::where('user_id', $user->id)
            ->whereNotNull('submitted_at')
            ->with('testSet:id,title,test_type')
            ->orderByDesc('submitted_at')
            ->take(3)
            ->get();

        // Lessons due today
        $lessonsDueToday = collect($inProgress)->sum(function ($course) {
            return $course['total_lessons'] - $course['completed_lessons'];
        });

        return response()->json([
            'user' => [
                'full_name' => $user->full_name,
                'jlpt_target_level' => $user->jlpt_target_level,
            ],
            'xp' => $xpStats,
            'sparkline' => $sparkline,
            'continue_course' => $continueCourse,
            'in_progress' => $inProgress,
            'flashcards_due' => $flashcardsDue,
            'recent_tests' => $recentTests,
            'lessons_due_today' => $lessonsDueToday,
        ]);
    }
}