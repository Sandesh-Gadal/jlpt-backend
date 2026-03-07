<?php

namespace App\Http\Controllers;

use App\Models\TestSet;
use App\Models\TestAttempt;
use App\Models\AttemptAnswer;
use App\Models\Question;
use App\Services\XpService;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function __construct(private XpService $xp) {}

    /**
     * GET /tests
     * List available test sets for the user
     */
    public function index(Request $request)
    {
        $tests = TestSet::where('is_published', true)
            ->with('level')
            ->when($request->level, fn($q) => $q->whereHas('level', fn($q2) => $q2->where('code', $request->level)))
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($test) use ($request) {
                $lastAttempt = TestAttempt::where('user_id', $request->user()->id)
                    ->where('test_set_id', $test->id)
                    ->whereNotNull('submitted_at')
                    ->orderByDesc('submitted_at')
                    ->first();
                $test->last_attempt = $lastAttempt;
                return $test;
            });

        return response()->json(['tests' => $tests]);
    }

    /**
     * POST /tests/{testSetId}/start
     * Creates a new test attempt, returns questions (shuffled, answers hidden)
     */
    public function start(Request $request, string $testSetId)
    {
        $user    = $request->user();
        $testSet = TestSet::with(['questionBanks.questions'])->findOrFail($testSetId);

        // Check for existing in-progress attempt
        $existing = TestAttempt::where('user_id', $user->id)
            ->where('test_set_id', $testSetId)
            ->where('status', 'in_progress')
            ->first();

        if ($existing) {
            return response()->json([
                'attempt'   => $existing,
                'questions' => $this->getQuestionsForAttempt($existing),
                'resumed'   => true,
            ]);
        }

        // Create new attempt
        $attempt = TestAttempt::create([
            'test_set_id' => $testSetId,
            'user_id'     => $user->id,
            'status'      => 'in_progress',
            'started_at'  => now(),
        ]);

        // Sample questions from question banks
        $questions = collect();
        foreach ($testSet->questionBanks as $bank) {
            $sampled = $bank->questions()
                ->where('questions.is_published', true)
                ->inRandomOrder()
                ->take(20) // 20 per section
                ->get();
            $questions = $questions->merge($sampled);
        }

        // Initialise blank answer rows
        foreach ($questions as $q) {
            AttemptAnswer::create([
                'attempt_id'  => $attempt->id,
                'question_id' => $q->id,
            ]);
        }

        return response()->json([
            'attempt'   => $attempt,
            'test_set'  => $testSet->only(['id', 'title', 'test_type', 'time_limit_seconds', 'passing_score_percent']),
            'questions' => $questions->map(fn($q) => $this->sanitiseQuestion($q)),
            'resumed'   => false,
        ]);
    }

    /**
     * POST /tests/attempts/{attemptId}/answer
     * Body: { "question_id": "...", "selected_answer": "A", "time_spent_seconds": 30 }
     */
    public function answer(Request $request, string $attemptId)
    {
        $request->validate([
            'question_id'        => 'required|uuid',
            'selected_answer'    => 'nullable|string|max:10',
            'time_spent_seconds' => 'nullable|integer|min:0',
            'is_flagged'         => 'boolean',
        ]);

        $attempt = TestAttempt::where('id', $attemptId)
            ->where('user_id', $request->user()->id)
            ->where('status', 'in_progress')
            ->firstOrFail();

        $row = AttemptAnswer::where('attempt_id', $attempt->id)
            ->where('question_id', $request->question_id)
            ->firstOrFail();

        $row->update([
            'selected_answer'    => $request->selected_answer,
            'is_flagged'         => $request->boolean('is_flagged', $row->is_flagged),
            'time_spent_seconds' => $request->time_spent_seconds,
        ]);

        return response()->json(['saved' => true, 'answer' => $row]);
    }

    /**
     * POST /tests/attempts/{attemptId}/submit
     * Grades the test, awards XP, returns results
     */
    public function submit(Request $request, string $attemptId)
    {
        $user    = $request->user();
        $attempt = TestAttempt::where('id', $attemptId)
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->with('testSet', 'answers.question')
            ->firstOrFail();

        // Grade each answer
        $correct = 0;
        $total   = $attempt->answers->count();

        foreach ($attempt->answers as $answer) {
            $isCorrect = $answer->selected_answer === $answer->question->correct_answer;
            $answer->update(['is_correct' => $isCorrect]);
            if ($isCorrect) $correct++;
        }

        $scorePercent = $total > 0 ? round(($correct / $total) * 100, 2) : 0;
        $passed       = $scorePercent >= $attempt->testSet->passing_score_percent;
        $xpAwarded    = $passed
            ? $attempt->testSet->xp_reward_pass
            : $attempt->testSet->xp_reward_fail;

        $attempt->update([
            'status'        => 'graded',
            'score_percent' => $scorePercent,
            'passed'        => $passed,
            'xp_awarded'    => $xpAwarded,
            'submitted_at'  => now(),
        ]);

        // Award XP
        $this->xp->award(
            $user,
            $passed ? 'test_pass' : 'test_fail',
            $attempt->id,
            $xpAwarded
        );

        return response()->json([
            'attempt'       => $attempt->fresh(),
            'score_percent' => $scorePercent,
            'passed'        => $passed,
            'correct'       => $correct,
            'total'         => $total,
            'xp_awarded'    => $xpAwarded,
        ]);
    }

    /**
     * GET /tests/attempts/{attemptId}/results
     * Full results with correct answers and explanations
     */
    public function results(Request $request, string $attemptId)
    {
        $attempt = TestAttempt::where('id', $attemptId)
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['graded', 'submitted'])
            ->with([
                'testSet',
                'answers' => fn($q) => $q->with('question'),
            ])
            ->firstOrFail();

        return response()->json(['attempt' => $attempt]);
    }

    // ── Helpers ─────────────────────────────────────────────────

    private function sanitiseQuestion($q): array
    {
        return [
            'id'            => $q->id,
            'question_type' => $q->question_type,
            'prompt'        => $q->prompt,
            'audio_url'     => $q->audio_url,
            'options'       => $q->options,
            'difficulty'    => $q->difficulty,
            // NOTE: correct_answer intentionally omitted until results
        ];
    }

    private function getQuestionsForAttempt(TestAttempt $attempt): \Illuminate\Support\Collection
    {
        return AttemptAnswer::where('attempt_id', $attempt->id)
            ->with('question')
            ->get()
            ->map(fn($a) => $this->sanitiseQuestion($a->question));
    }
}
