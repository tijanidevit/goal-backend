<?php

namespace App\Services;

use App\Models\Goal;
use Carbon\Carbon;

class ProbabilityEngine
{
    private ForecastEngine $forecastEngine;

    public function __construct(ForecastEngine $forecastEngine)
    {
        $this->forecastEngine = $forecastEngine;
    }

    /**
     * Calculates the probability (0-100%) of achieving the goal by the target date.
     *
     * @param Goal $goal
     * @return int
     */
    public function calculateProbability(Goal $goal): int
    {
        $currentSavings = $goal->contributions()->sum('amount');
        
        if ($currentSavings >= $goal->target_amount) {
            return 100;
        }

        $estimatedDate = $this->forecastEngine->calculateEstimatedCompletionDate($goal);

        if (!$estimatedDate) {
            return 0; // No plan to reach goal
        }

        $targetDate = Carbon::parse($goal->target_date)->startOfDay();
        $estimatedDate = $estimatedDate->copy()->startOfDay();

        if ($estimatedDate->lessThanOrEqualTo($targetDate)) {
            return 100;
        }

        $createdAt = $goal->created_at ?? now();
        $totalPlannedMonths = max(1, $createdAt->diffInMonths($targetDate));
        
        $monthsLate = $targetDate->diffInMonths($estimatedDate, false);
        
        $latenessRatio = $monthsLate / $totalPlannedMonths;
        
        $probability = 100 - ($latenessRatio * 100);

        if ($probability < 0) {
            return 0;
        }

        return (int) round($probability);
    }
}
