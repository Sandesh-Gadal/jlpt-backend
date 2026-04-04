<?php

namespace App\Http\Controllers;

use App\Models\AttemptAnswer;
use App\Models\TestAttempt;
use App\Models\TestSet;
use App\Services\XpService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TestController extends Controller
{
    public function __construct(private XpService $xp) {}

    public function index(Request $request)
    {
        $user = $request->user();

        $tests = TestSet::query()
            ->where('is_published', true)
            ->with([
                'level:id,code,name_en',
                'questionBanks:id,test_set_id,name,section_type,question_count',
            ])
            ->when(
                $request->level,
                fn ($q) => $q->whereHas('level', fn ($q2) => $q2->where('code', $request->level))
            )
            ->latest()
            ->get()
            ->map(function (TestSet $test) use ($user) {
                $lastAttempt = TestAttempt::query()
                    ->where('user_id', $user->id)
                    ->where('test_set_id', $test->id)
                    ->whereNotNull('submitted_at')
                    ->latest('submitted_at')
                    ->first();

                $questionCount = (int) $test->questionBanks->sum('question_count');

                return [
                    'id' => $test->id,
                    'title' => $test->title,
                    'description' => $test->description ?? '',
                    'test_type' => $test->test_type,
                    'time_limit_seconds' => $test->time_limit_seconds,
                    'passing_score_percent' => $test->passing_score_percent,
                    'xp_reward_pass' => $test->xp_reward_pass,
                    'xp_reward_fail' => $test->xp_reward_fail,
                    'is_published' => $test->is_published,
                    'level' => [
                        'code' => $test->level?->code,
                        'name_en' => $test->level?->name_en,
                    ],
                    'category' => $this->deriveCategory($test),
                    'question_count' => $questionCount,
                    'sections' => $test->questionBanks->values()->map(function ($bank) use ($test, $questionCount) {
                        $seconds = $questionCount > 0
                            ? (int) round(($bank->question_count / $questionCount) * $test->time_limit_seconds)
                            : 0;

                        return [
                            'id' => $bank->id,
                            'name' => $bank->name,
                            'section_type' => $bank->section_type,
                            'question_count' => $bank->question_count,
                            'time_minutes' => max(1, (int) ceil($seconds / 60)),
                            'has_audio' => $bank->section_type === 'listening',
                        ];
                    }),
                    'last_attempt' => $lastAttempt ? [
                        'id' => $lastAttempt->id,
                        'score_percent' => (float) $lastAttempt->score_percent,
                        'passed' => (bool) $lastAttempt->passed,
                        'submitted_at' => optional($lastAttempt->submitted_at)?->toISOString(),
                        'time_taken_seconds' => $this->calculateTimeTakenSeconds($lastAttempt),
                    ] : null,
                ];
            });

        return response()->json(['tests' => $tests]);
    }

public function start(Request $request, string $testSetId)
{
    $user = $request->user();

    $testSet = TestSet::query()
    ->with([
        'level:id,code,name_en',
        'questionBanks' => fn ($q) => $q->select('id', 'test_set_id', 'name', 'section_type', 'question_count'),
        'questionBanks.questions' => fn ($q) => $q->where('is_published', true),
    ])
    ->findOrFail($testSetId);

    $existing = TestAttempt::query()
        ->where('user_id', $user->id)
        ->where('test_set_id', $testSetId)
        ->where('status', 'in_progress')
        ->with([
            'answers.question.questionBank',
        ])
        ->first();

    if ($existing) {
        $existingAnswers = $existing->answers;

        if ($existingAnswers->count() > 0) {
            return response()->json([
                'attempt' => $existing,
                'test_set' => $this->serializeTestSet($testSet),
                'questions' => $this->getQuestionsForAttempt($existing),
                'saved_answers' => $existingAnswers->map(fn ($a) => [
                    'question_id' => $a->question_id,
                    'selected_answer' => $a->selected_answer,
                    'is_flagged' => (bool) $a->is_flagged,
                    'time_spent_seconds' => (int) ($a->time_spent_seconds ?? 0),
                ])->values(),
                'resumed' => true,
            ]);
        }

        // Broken in-progress attempt with no answers: remove it and recreate
        $existing->delete();
    }

    $questions = collect();

    foreach ($testSet->questionBanks as $bank) {
        $count = max(1, (int) $bank->question_count);

        $sampled = $bank->questions()
            ->where('is_published', true)
            ->inRandomOrder()
            ->take($count)
            ->get();

        $questions = $questions->merge($sampled);
    }

    if ($questions->isEmpty()) {
        return response()->json([
            'message' => 'No published questions available for this test.',
        ], 422);
    }

    $attempt = TestAttempt::create([
        'test_set_id' => $testSetId,
        'user_id' => $user->id,
        'status' => 'in_progress',
        'started_at' => now(),
    ]);

    foreach ($questions as $q) {
        AttemptAnswer::create([
            'attempt_id' => $attempt->id,
            'question_id' => $q->id,
            'selected_answer' => null,
            'is_correct' => null,
            'is_flagged' => false,
            'time_spent_seconds' => 0,
        ]);
    }

    return response()->json([
        'attempt' => $attempt->fresh(),
        'test_set' => $this->serializeTestSet($testSet),
        'questions' => $questions->values()->map(fn ($q) => $this->sanitiseQuestion($q)),
        'saved_answers' => [],
        'resumed' => false,
    ]);
}

    public function answer(Request $request, string $attemptId)
    {
        $validated = $request->validate([
            'question_id' => 'required|uuid',
            'selected_answer' => 'nullable|string|max:50',
            'time_spent_seconds' => 'nullable|integer|min:0',
            'is_flagged' => 'nullable|boolean',
        ]);

        $attempt = TestAttempt::query()
            ->where('id', $attemptId)
            ->where('user_id', $request->user()->id)
            ->where('status', 'in_progress')
            ->firstOrFail();

        $row = AttemptAnswer::query()
            ->where('attempt_id', $attempt->id)
            ->where('question_id', $validated['question_id'])
            ->firstOrFail();

        $row->update([
            'selected_answer' => $validated['selected_answer'] ?? $row->selected_answer,
            'is_flagged' => array_key_exists('is_flagged', $validated)
                ? (bool) $validated['is_flagged']
                : $row->is_flagged,
            'time_spent_seconds' => max(
                (int) ($row->time_spent_seconds ?? 0),
                (int) ($validated['time_spent_seconds'] ?? 0)
            ),
        ]);

        return response()->json([
            'saved' => true,
            'answer' => $row->fresh(),
        ]);
    }

    public function submit(Request $request, string $attemptId)
    {
        $user = $request->user();

        $attempt = TestAttempt::query()
            ->where('id', $attemptId)
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->with([
                'testSet',
                'answers.question.questionBank',
            ])
            ->firstOrFail();

        $correct = 0;
        $total = $attempt->answers->count();

        foreach ($attempt->answers as $answer) {
            $isCorrect = $answer->selected_answer !== null
                && $answer->selected_answer === $answer->question->correct_answer;

            $answer->update([
                'is_correct' => $isCorrect,
            ]);

            if ($isCorrect) {
                $correct++;
            }
        }

        $scorePercent = $total > 0 ? round(($correct / $total) * 100, 2) : 0.0;
        $passed = $scorePercent >= $attempt->testSet->passing_score_percent;
        $xpAwarded = $passed
            ? $attempt->testSet->xp_reward_pass
            : $attempt->testSet->xp_reward_fail;

        $attempt->update([
            'status' => 'graded',
            'score_percent' => $scorePercent,
            'passed' => $passed,
            'xp_awarded' => $xpAwarded,
            'submitted_at' => now(),
        ]);

        $this->xp->award(
            $user,
            $passed ? 'test_pass' : 'test_fail',
            $attempt->id,
            $xpAwarded
        );

        return response()->json($this->buildResultPayload(
            $attempt->fresh(['testSet.level', 'answers.question.questionBank'])
        ));
    }

    public function results(Request $request, string $attemptId)
    {
        $attempt = TestAttempt::query()
            ->where('id', $attemptId)
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['graded', 'submitted'])
            ->with([
                'testSet.level',
                'answers.question.questionBank',
            ])
            ->firstOrFail();

        return response()->json($this->buildResultPayload($attempt));
    }

    private function buildResultPayload(TestAttempt $attempt): array
    {
        $answers = $attempt->answers->values();
        $correct = $answers->where('is_correct', true)->count();
        $total = $answers->count();
        $timeTakenSeconds = $this->calculateTimeTakenSeconds($attempt);

        $sectionScores = $answers
            ->groupBy(fn ($a) => $a->question->questionBank->name ?? 'Unknown')
            ->map(function (Collection $group, string $name) {
                $total = $group->count();
                $correct = $group->where('is_correct', true)->count();

                return [
                    'section_name' => $name,
                    'correct' => $correct,
                    'total' => $total,
                    'percent' => $total > 0 ? round(($correct / $total) * 100, 2) : 0,
                ];
            })
            ->values();

        $difficultyScores = $answers
            ->groupBy(fn ($a) => $a->question->difficulty ?? 'medium')
            ->map(function (Collection $group, string $difficulty) {
                $total = $group->count();
                $correct = $group->where('is_correct', true)->count();

                return [
                    'difficulty' => $difficulty,
                    'correct' => $correct,
                    'total' => $total,
                    'percent' => $total > 0 ? round(($correct / $total) * 100, 2) : 0,
                ];
            })
            ->values();

        $reviewAnswers = $answers->map(function ($a) {
            return [
                'question' => [
                    'id' => $a->question->id,
                    'section_id' => $a->question->questionBank?->id,
                    'question_type' => $a->question->question_type,
                    'prompt' => $a->question->prompt,
                    'audio_url' => $a->question->audio_url,
                    'options' => $this->normaliseOptions($a->question->options),
                    'difficulty' => $a->question->difficulty,
                    'correct_answer' => $a->question->correct_answer,
                    'explanation' => $a->question->explanation,
                ],
                'selected_option' => $a->selected_answer,
                'is_correct' => (bool) $a->is_correct,
                'is_flagged' => (bool) $a->is_flagged,
                'time_spent_seconds' => (int) ($a->time_spent_seconds ?? 0),
            ];
        })->values();

        return [
            'attempt' => [
                'id' => $attempt->id,
                'test_set_id' => $attempt->test_set_id,
                'user_id' => $attempt->user_id,
                'status' => $attempt->status,
                'started_at' => optional($attempt->started_at)?->toISOString(),
                'submitted_at' => optional($attempt->submitted_at)?->toISOString(),
                'score_percent' => (float) $attempt->score_percent,
                'passed' => (bool) $attempt->passed,
                'xp_awarded' => (int) $attempt->xp_awarded,
            ],
            'test_set' => [
                'id' => $attempt->testSet->id,
                'title' => $attempt->testSet->title,
                'description' => $attempt->testSet->description ?? '',
                'test_type' => $attempt->testSet->test_type,
                'time_limit_seconds' => $attempt->testSet->time_limit_seconds,
                'passing_score_percent' => $attempt->testSet->passing_score_percent,
                'xp_reward_pass' => $attempt->testSet->xp_reward_pass,
                'xp_reward_fail' => $attempt->testSet->xp_reward_fail,
                'level' => [
                    'code' => $attempt->testSet->level?->code,
                    'name_en' => $attempt->testSet->level?->name_en,
                ],
                'category' => $this->deriveCategory($attempt->testSet),
            ],
            'score_percent' => (float) $attempt->score_percent,
            'passed' => (bool) $attempt->passed,
            'correct' => $correct,
            'total' => $total,
            'xp_awarded' => (int) $attempt->xp_awarded,
            'time_taken_seconds' => $timeTakenSeconds,
            'section_scores' => $sectionScores,
            'difficulty_scores' => $difficultyScores,
            'review_answers' => $reviewAnswers,
        ];
    }

    private function sanitiseQuestion($q): array
    {
        return [
            'id' => $q->id,
            'section_id' => $q->question_bank_id,
            'question_type' => $q->question_type,
            'prompt' => $q->prompt,
            'audio_url' => $q->audio_url,
            'options' => $this->normaliseOptions($q->options),
            'difficulty' => $q->difficulty,
        ];
    }

    private function getQuestionsForAttempt(TestAttempt $attempt): Collection
    {
        return AttemptAnswer::query()
            ->where('attempt_id', $attempt->id)
            ->with('question.questionBank')
            ->get()
            ->map(fn ($a) => $this->sanitiseQuestion($a->question))
            ->values();
    }

    private function normaliseOptions($options): array
    {
        if (!is_array($options)) {
            return [];
        }

        $normalised = [];
        foreach ($options as $key => $value) {
            $normalised[] = [
                'id' => is_string($key) ? $key : (string) $value['id'],
                'text' => is_array($value) ? ($value['text'] ?? '') : (string) $value,
            ];
        }

        return $normalised;
    }

    private function calculateTimeTakenSeconds(TestAttempt $attempt): int
    {
        if ($attempt->started_at && $attempt->submitted_at) {
            return (int) $attempt->started_at->diffInSeconds($attempt->submitted_at);
        }

        return (int) $attempt->answers()->sum('time_spent_seconds');
    }

    private function deriveCategory(TestSet $test): string
    {
        $types = $test->questionBanks->pluck('section_type')->unique()->values();

        if ($types->count() > 1) {
            return 'Mixed';
        }

        return match ($types->first()) {
            'vocabulary' => 'Vocabulary',
            'grammar' => 'Grammar',
            'reading' => 'Reading',
            'listening' => 'Listening',
            default => 'Mixed',
        };
    }

    private function serializeTestSet(TestSet $testSet): array
    {
        $questionCount = (int) $testSet->questionBanks->sum('question_count');

        return [
            'id' => $testSet->id,
            'title' => $testSet->title,
            'description' => $testSet->description ?? '',
            'test_type' => $testSet->test_type,
            'time_limit_seconds' => $testSet->time_limit_seconds,
            'passing_score_percent' => $testSet->passing_score_percent,
            'xp_reward_pass' => $testSet->xp_reward_pass,
            'xp_reward_fail' => $testSet->xp_reward_fail,
            'level' => [
                'code' => $testSet->level?->code,
                'name_en' => $testSet->level?->name_en,
            ],
            'category' => $this->deriveCategory($testSet),
            'question_count' => $questionCount,
            'sections' => $testSet->questionBanks->values()->map(function ($bank) use ($testSet, $questionCount) {
                $seconds = $questionCount > 0
                    ? (int) round(($bank->question_count / $questionCount) * $testSet->time_limit_seconds)
                    : 0;

                return [
                    'id' => $bank->id,
                    'name' => $bank->name,
                    'section_type' => $bank->section_type,
                    'question_count' => $bank->question_count,
                    'time_minutes' => max(1, (int) ceil($seconds / 60)),
                    'has_audio' => $bank->section_type === 'listening',
                ];
            })->values(),
        ];
    }
}
