<?php

namespace App\Http\Middleware;

use App\Models\ContentAccessRule;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureGate
{
    public function handle(Request $request, Closure $next, string $featureKey): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Load tenant and plan
        $user->loadMissing('tenant.plan');
        $planType = $user->tenant->plan->plan_type ?? 'free';

        // Cache key — per user per feature
        $cacheKey = "feature_gate:{$user->id}:{$featureKey}";

        $result = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($planType, $featureKey) {
            return $this->checkAccess($planType, $featureKey);
        });

        if (!$result['allowed']) {
            return response()->json([
                'message'          => 'This feature requires an upgrade.',
                'feature_key'      => $featureKey,
                'your_plan'        => $planType,
                'required_plan'    => $result['required_plan'],
                'upgrade_prompt'   => $result['upgrade_prompt'],
            ], 403);
        }

        return $next($request);
    }

    private function checkAccess(string $planType, string $featureKey): array
    {
        // Plan hierarchy
        $hierarchy = [
            'free'        => 0,
            'individual'  => 1,
            'team'        => 2,
            'institution' => 3,
        ];

        // Feature to minimum plan mapping
        $featureMap = [
            'n5_content'        => 'free',
            'n4_content'        => 'individual',
            'n3_content'        => 'individual',
            'n2_content'        => 'individual',
            'n1_content'        => 'individual',
            'mock_exams'        => 'individual',
            'audio_lessons'     => 'individual',
            'flashcards'        => 'free',
            'basic_tests'       => 'free',
            'full_analytics'    => 'individual',
            'assign_tests'      => 'team',
            'institution_admin' => 'institution',
            'bulk_export'       => 'institution',
            'sso'               => 'institution',
        ];

        $requiredPlan  = $featureMap[$featureKey] ?? 'individual';
        $userLevel     = $hierarchy[$planType]     ?? 0;
        $requiredLevel = $hierarchy[$requiredPlan] ?? 1;

        return [
            'allowed'        => $userLevel >= $requiredLevel,
            'required_plan'  => $requiredPlan,
            'upgrade_prompt' => "upgrade_to_{$requiredPlan}",
        ];
    }
}