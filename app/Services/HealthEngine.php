<?php

namespace App\Services;

use App\Models\Goal;
use Carbon\Carbon;

use App\Enums\EnumGoalHealthLabel;

class HealthEngine
{
    private ForecastEngine $forecastEngine;

    public function __construct(ForecastEngine $forecastEngine)
    {
        $this->forecastEngine = $forecastEngine;
    }

    /**
     * Determines the health status of a goal.
     *
     * @param Goal $goal
     * @return string
     */
    public function determineHealthStatus(Goal $goal): string
    {
        $currentSavings = $goal->contributions()->sum('amount');
        
        if ($currentSavings >= $goal->target_amount) {
            return EnumGoalHealthLabel::ACHIEVED->value;
        }

        $estimatedDate = $this->forecastEngine->calculateEstimatedCompletionDate($goal);

        if (!$estimatedDate) {
            return EnumGoalHealthLabel::NO_PLAN->value;
        }

        $targetDate = Carbon::parse($goal->target_date)->startOfDay();
        $estimatedDate = $estimatedDate->copy()->startOfDay();

        if ($estimatedDate->lessThanOrEqualTo($targetDate)) {
            return EnumGoalHealthLabel::ON_TRACK->value;
        }

        $monthsLate = $targetDate->diffInMonths($estimatedDate, false);

        if ($monthsLate > 3) {
            return EnumGoalHealthLabel::AT_RISK->value;
        }

        return EnumGoalHealthLabel::BEHIND->value;
    }
}
