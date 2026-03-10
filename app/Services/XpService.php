<?php

namespace App\Services;

use App\Models\User;
use App\Models\Streak;
use App\Models\UserXpLedger;

class XpService
{
    /**
     * Award XP to a user and record it in the ledger.
     * source_type: lesson | test_pass | test_fail | streak | referral | bonus
     */
    public function award(User $user, string $sourceType, ?string $sourceId, int $amount): UserXpLedger
    {
        // Get current total
        $currentBalance = UserXpLedger::where('user_id', $user->id)->sum('xp_amount');

        $entry = UserXpLedger::create([
            'user_id'          => $user->id,
            'source_type'      => $sourceType,
            'source_id'        => $sourceId,
            'xp_amount'        => $amount,
            'xp_balance_after' => $currentBalance + $amount,
            'earned_at'        => now(),
        ]);

        // Update streak
        $this->updateStreak($user);

        return $entry;
    }

    /**
     * Get a user's total XP
     */
    public function getTotal(User $user): int
    {
        return (int) UserXpLedger::where('user_id', $user->id)->sum('xp_amount');
    }

    /**
     * Get the user's current streak and update it
     */
    public function updateStreak(User $user): Streak
    {
        $streak = Streak::firstOrCreate(
            ['user_id' => $user->id],
            ['current_streak' => 0, 'longest_streak' => 0]
        );

        $today     = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $lastDate  = $streak->last_activity_date?->toDateString();

        if ($lastDate === $today) {
            // Already recorded today — no change
            return $streak;
        }

        if ($lastDate === $yesterday) {
            // Consecutive day — increment
            $streak->current_streak++;
        } else {
            // Streak broken (or first activity)
            $streak->current_streak = 1;
        }

        $streak->longest_streak    = max($streak->longest_streak, $streak->current_streak);
        $streak->last_activity_date = $today;
        $streak->save();

        // Bonus XP for streak milestones
        if (in_array($streak->current_streak, [3, 7, 14, 30, 60, 100])) {
            $bonus = $streak->current_streak * 5;
            UserXpLedger::create([
                'user_id'          => $user->id,
                'source_type'      => 'streak',
                'source_id'        => null,
                'xp_amount'        => $bonus,
                'xp_balance_after' => $this->getTotal($user) + $bonus,
                'earned_at'        => now(),
            ]);
        }

        return $streak;
    }

    /**
     * Get full XP + progress summary for dashboard
     */
    public function getDashboardStats(User $user): array
    {
        $totalXp   = $this->getTotal($user);
        $streak    = Streak::where('user_id', $user->id)->first();
        $level     = $this->xpToLevel($totalXp);
        $nextLevel = $this->levelThreshold($level + 1);
        $thisLevel = $this->levelThreshold($level);
        $progress  = $nextLevel > $thisLevel
            ? round((($totalXp - $thisLevel) / ($nextLevel - $thisLevel)) * 100)
            : 100;

        return [
            'total_xp'       => $totalXp,
            'level'          => $level,
            'level_label'    => "Level {$level}",
            'progress_pct'   => $progress,
            'xp_to_next'     => max(0, $nextLevel - $totalXp),
            'streak'         => $streak?->current_streak ?? 0,
            'longest_streak' => $streak?->longest_streak ?? 0,
        ];
    }

    private function xpToLevel(int $xp): int
    {
        // Level thresholds: 0, 100, 300, 600, 1000, 1500, 2100, 2800...
        $level = 1;
        while ($xp >= $this->levelThreshold($level + 1)) {
            $level++;
            if ($level >= 50) break;
        }
        return $level;
    }

    private function levelThreshold(int $level): int
    {
        // Quadratic growth: level * level * 50
        return ($level - 1) * ($level - 1) * 50;
    }
}
