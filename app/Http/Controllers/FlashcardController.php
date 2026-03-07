<?php

namespace App\Http\Controllers;

use App\Models\Flashcard;
use App\Models\FlashcardReview;
use App\Services\SrsService;
use Illuminate\Http\Request;

class FlashcardController extends Controller
{
    public function __construct(private SrsService $srs) {}

    /**
     * GET /flashcards
     * List all flashcards with user's SRS state and summary stats
     */
    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Flashcard::with('level')
            ->where('is_published', true)
            ->when($request->level,    fn($q) => $q->whereHas('level', fn($q2) => $q2->where('code', $request->level)))
            ->when($request->category, fn($q) => $q->where('category', $request->category));

        // Attach user's SRS state
        $cards = $query->get()->map(function ($card) use ($user) {
            $review = FlashcardReview::where('user_id', $user->id)
                ->where('flashcard_id', $card->id)->first();
            $card->user_review = $review ? [
                'next_review_at' => $review->next_review_at,
                'interval'       => $review->interval,
                'easiness'       => $review->easiness,
                'repetitions'    => $review->repetitions,
                'last_rating'    => $review->last_rating,
                'is_due'         => $review->next_review_at->isPast(),
            ] : null;
            return $card;
        });

        // Summary stats
        $total    = $cards->count();
        $due      = $cards->filter(fn($c) => $c->user_review && $c->user_review['is_due'])->count();
        $new      = $cards->filter(fn($c) => !$c->user_review)->count();
        $mastered = $cards->filter(fn($c) => $c->user_review && $c->user_review['repetitions'] >= 5)->count();

        return response()->json([
            'cards' => $cards,
            'stats' => compact('total', 'due', 'new', 'mastered'),
        ]);
    }

    /**
     * GET /flashcards/due
     * Returns cards due for review (or new cards if none due)
     */
    public function due(Request $request)
    {
        $user  = $request->user();
        $limit = (int) $request->input('limit', 20);

        // IDs already reviewed by this user
        $reviewedIds = FlashcardReview::where('user_id', $user->id)
            ->pluck('flashcard_id');

        // 1. Due cards (already seen, interval expired)
        $due = FlashcardReview::where('user_id', $user->id)
            ->where('next_review_at', '<=', now())
            ->with('flashcard.level')
            ->orderBy('next_review_at')
            ->take($limit)
            ->get()
            ->pluck('flashcard');

        $needed = $limit - $due->count();

        // 2. New cards (never seen) to fill remainder
        $new = collect();
        if ($needed > 0) {
            $new = Flashcard::where('is_published', true)
                ->whereNotIn('id', $reviewedIds)
                ->when($request->level, fn($q) => $q->whereHas('level', fn($q2) => $q2->where('code', $request->level)))
                ->with('level')
                ->take($needed)
                ->get();
        }

        return response()->json([
            'cards'     => $due->merge($new)->values(),
            'due_count' => $due->count(),
            'new_count' => $new->count(),
        ]);
    }

    /**
     * POST /flashcards/{id}/rate
     * Body: { "rating": "again|hard|good|easy" }
     */
    public function rate(Request $request, string $id)
    {
        $request->validate(['rating' => 'required|in:again,hard,good,easy']);

        $user      = $request->user();
        $card      = Flashcard::findOrFail($id);
        $numRating = $this->srs->buttonToRating($request->rating);

        // Get or create review record
        $review = FlashcardReview::firstOrNew([
            'user_id'      => $user->id,
            'flashcard_id' => $card->id,
        ]);

        // Apply SM-2
        $result = $this->srs->calculate(
            $numRating,
            $review->repetitions ?? 0,
            $review->easiness    ?? 2.5,
            $review->interval    ?? 1
        );

        $review->fill([...$result, 'last_rating' => $numRating]);
        $review->save();

        return response()->json([
            'review'     => $review,
            'next_label' => $this->intervalLabel($result['interval']),
        ]);
    }

    private function intervalLabel(int $days): string
    {
        if ($days < 1)   return 'in a few minutes';
        if ($days === 1) return 'tomorrow';
        if ($days < 7)   return "in {$days} days";
        if ($days < 30)  return 'in ' . round($days / 7) . ' weeks';
        return 'in ' . round($days / 30) . ' months';
    }
}
