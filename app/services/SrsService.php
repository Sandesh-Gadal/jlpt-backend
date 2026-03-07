<?php

namespace App\Services;

class SrsService
{
    /**
     * SM-2 algorithm
     * Rating 0–5: 0=blackout, 1=wrong, 2=wrong but familiar,
     *             3=correct hard, 4=correct, 5=perfect
     *
     * Returns: ['repetitions', 'easiness', 'interval', 'next_review_at']
     */
    public function calculate(
        int   $rating,
        int   $repetitions,
        float $easiness,
        int   $interval
    ): array {
        // Failed recall — reset
        if ($rating < 3) {
            $repetitions = 0;
            $interval    = 1;
        } else {
            // Successful recall
            $interval = match ($repetitions) {
                0       => 1,
                1       => 6,
                default => (int) round($interval * $easiness),
            };
            $repetitions++;
        }

        // Update easiness factor (EF)
        $easiness = $easiness + (0.1 - (5 - $rating) * (0.08 + (5 - $rating) * 0.02));
        $easiness = max(1.3, $easiness); // EF never goes below 1.3

        $nextReview = now()->addDays($interval);

        return [
            'repetitions'    => $repetitions,
            'easiness'       => round($easiness, 4),
            'interval'       => $interval,
            'next_review_at' => $nextReview,
        ];
    }

    /**
     * Convenience: convert user button press to SM-2 rating
     * 'again' → 0, 'hard' → 3, 'good' → 4, 'easy' → 5
     */
    public function buttonToRating(string $button): int
    {
        return match ($button) {
            'again' => 0,
            'hard'  => 3,
            'good'  => 4,
            'easy'  => 5,
            default => 4,
        };
    }
}
