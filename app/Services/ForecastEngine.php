<?php

namespace App\Services;

use App\Models\Goal;
use Carbon\Carbon;

use App\Enums\EnumContributionFrequency;

class ForecastEngine
{
    /**
     * Calculates the estimated completion date of a goal.
     * 
     * @param Goal $goal
     * @return Carbon|null Null if there is no planned contribution.
     */
    public function calculateEstimatedCompletionDate(Goal $goal): ?Carbon
    {
        $currentSavings = $goal->contributions()->sum('amount');
        $remainingAmount = $goal->target_amount - $currentSavings;

        if ($remainingAmount <= 0) {
            return now();
        }

        $monthlyContribution = $this->calculateMonthlyContribution($goal);

        if ($monthlyContribution <= 0) {
            return null; // Cannot forecast without a positive contribution rate
        }

        $monthsLeft = ceil($remainingAmount / $monthlyContribution);
        
        return now()->addMonths((int) $monthsLeft);
    }

    /**
     * Calculates the total planned monthly contribution.
     */
    private function calculateMonthlyContribution(Goal $goal): float
    {
        $plans = $goal->contributionPlans()->where('active', true)->get();
        $totalMonthly = 0;

        foreach ($plans as $plan) {
            if ($plan->frequency === EnumContributionFrequency::MONTHLY) {
                $totalMonthly += $plan->amount;
            } elseif ($plan->frequency === EnumContributionFrequency::WEEKLY) {
                $totalMonthly += $plan->amount * 4.33; // Approx weeks in a month
            }
        }

        return $totalMonthly;
    }
}
