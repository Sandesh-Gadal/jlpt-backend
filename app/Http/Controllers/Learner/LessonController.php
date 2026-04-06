<?php

namespace App\Http\Controllers\Learner;

use App\Http\Controllers\Controller;
use App\Models\ContentAccessRule;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    // ── GET /api/v1/lessons/{id} ───────────────────────────
public function show(Request $request, string $id): JsonResponse
{
    $user     = $request->user();
    $planType = $user->tenant->plan->plan_type ?? 'free';

    $lesson = Lesson::with('course.jlptLevel')
        ->where('is_published', true)
        ->findOrFail($id);

    // Check access using FeatureGateService
    $rule      = ContentAccessRule::where('content_type', 'lesson')
        ->where('content_id', $lesson->id)
        ->first();
    $hasAccess = !$rule || $rule->isAccessibleBy($planType);

    if (!$hasAccess) {
        return response()->json([
            'message'          => 'This lesson requires an upgrade.',
            'feature_key'      => 'lesson_access',
            'your_plan'        => $planType,
            'required_plan'    => $rule->min_plan_type,
            'upgrade_prompt'   => 'upgrade_to_' . $rule->min_plan_type,
        ], 403);
    }

    $completed = LessonCompletion::where('user_id', $user->id)
        ->where('lesson_id', $lesson->id)
        ->exists();

    return response()->json([
        'lesson' => [
            'id'                => $lesson->id,
            'title'             => $lesson->title,
            'lesson_type'       => $lesson->lesson_type,
            'content'           => $lesson->content_json,
            'estimated_minutes' => $lesson->estimated_minutes,
            'xp_reward'         => $lesson->xp_reward,
            'video_url'         => $lesson->video_url,
            'sort_order' => $lesson->sort_order,
            'audio_url'         => $lesson->audio_url,
            'course'            => [
                'id'    => $lesson->course->id,
                'title' => $lesson->course->title,
                'level' => $lesson->course->jlptLevel->code,
            ],
            'already_completed' => $completed,
        ],
    ]);
}

    // ── POST /api/v1/lessons/{id}/complete ─────────────────
    public function complete(Request $request, string $id): JsonResponse
    {
        $user   = $request->user();
        $lesson = Lesson::where('is_published', true)->findOrFail($id);

        // Check if already completed
        $alreadyDone = LessonCompletion::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->exists();

        if ($alreadyDone) {
            return response()->json([
                'message'     => 'Lesson already completed.',
                'xp_awarded'  => 0,
                'xp_total'    => $this->getUserXp($user->id),
            ]);
        }

        // Record completion
        LessonCompletion::create([
            'user_id'     => $user->id,
            'lesson_id'   => $lesson->id,
            'xp_awarded'  => $lesson->xp_reward,
            'completed_at'=> now(),
        ]);

        // Award XP
        $this->awardXp($user->id, $lesson->xp_reward, 'lesson', $lesson->id);

        return response()->json([
            'message'    => 'Lesson completed!',
            'xp_awarded' => $lesson->xp_reward,
            'xp_total'   => $this->getUserXp($user->id),
        ]);
    }

    // ── Private helpers ────────────────────────────────────
    private function getUserXp(string $userId): int
    {
        return \App\Models\UserXpLedger::where('user_id', $userId)
            ->latest('earned_at')
            ->value('xp_balance_after') ?? 0;
    }

    private function awardXp(string $userId, int $amount, string $sourceType, string $sourceId): void
    {
        $currentBalance = $this->getUserXp($userId);

        \App\Models\UserXpLedger::create([
            'user_id'          => $userId,
            'source_type'      => $sourceType,
            'source_id'        => $sourceId,
            'xp_amount'        => $amount,
            'xp_balance_after' => $currentBalance + $amount,
            'earned_at'        => now(),
        ]);
    }
}
