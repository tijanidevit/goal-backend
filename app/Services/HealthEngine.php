<?php

namespace App\Services;

use App\Models\Goal;
use Carbon\Carbon;

class HealthEngine
{
    private ForecastEngine $forecastEngine;

    public function __construct(ForecastEngine $forecastEngine)
    {
        $this->forecastEngine = $forecastEngine;
    }

    /**
     * Determines the health status of a goal.
     * Statuses: 'On Track', 'Behind', 'At Risk', 'No Plan', 'Achieved'
     *
     * @param Goal $goal
     * @return string
     */
    public function determineHealthStatus(Goal $goal): string
    {
        $currentSavings = $goal->contributions()->sum('amount');
        
        if ($currentSavings >= $goal->target_amount) {
            return 'Achieved';
        }

        $estimatedDate = $this->forecastEngine->calculateEstimatedCompletionDate($goal);

        if (!$estimatedDate) {
            return 'No Plan';
        }

        $targetDate = Carbon::parse($goal->target_date)->startOfDay();
        $estimatedDate = $estimatedDate->copy()->startOfDay();

        if ($estimatedDate->lessThanOrEqualTo($targetDate)) {
            return 'On Track';
        }

        $monthsLate = $targetDate->diffInMonths($estimatedDate, false);

        if ($monthsLate > 3) {
            return 'At Risk';
        }

        return 'Behind';
    }
}
