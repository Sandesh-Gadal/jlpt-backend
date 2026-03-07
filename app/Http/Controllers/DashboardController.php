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
     * Returns all data needed by S06 dashboard
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
            return ['date' => $date, 'count' => $weeklyActivity[$date]->count ?? 0];
        });

        // In-progress courses
        $inProgress = Course::whereHas('lessons.completions', fn($q) => $q->where('user_id', $user->id))
            ->with(['lessons' => fn($q) => $q->select('id', 'course_id', 'title', 'sort_order', 'xp_reward')])
            ->take(4)
            ->get()
            ->map(function ($course) use ($user) {
                $total     = $course->lessons->count();
                $completed = LessonCompletion::where('user_id', $user->id)
                    ->whereIn('lesson_id', $course->lessons->pluck('id'))
                    ->count();
                $course->progress_pct      = $total > 0 ? round(($completed / $total) * 100) : 0;
                $course->completed_lessons = $completed;
                $course->total_lessons     = $total;
                return $course;
            });

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

        return response()->json([
            'xp'             => $xpStats,
            'sparkline'      => $sparkline,
            'in_progress'    => $inProgress,
            'flashcards_due' => $flashcardsDue,
            'recent_tests'   => $recentTests,
        ]);
    }
}
