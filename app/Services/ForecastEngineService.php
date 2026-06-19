<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\FinancialProfile;
use Carbon\Carbon;

class ForecastEngineService
{
    /**
     * Determine whether a user can achieve a goal before the target date.
     * 
     * @param Goal $goal
     * @param FinancialProfile|null $financialProfile
     * @return array
     */
    public function forecast(Goal $goal, ?FinancialProfile $financialProfile): array
    {
        $targetAmount = (float) $goal->target_amount;
        $currentSavings = (float) $goal->contributions()->sum('amount');
        $remainingAmount = max(0, $targetAmount - $currentSavings);
        
        $targetDate = Carbon::parse($goal->target_date)->startOfDay();
        $today = now()->startOfDay();
        
        // Months remaining
        $monthsRemaining = $today->diffInMonths($targetDate, false);
        if ($monthsRemaining < 0) {
            $monthsRemaining = 0; // Target date passed
        }
        
        // Required Monthly Savings
        $requiredMonthlySavings = $monthsRemaining > 0 ? $remainingAmount / $monthsRemaining : $remainingAmount;
        
        // Available Monthly Savings
        $availableMonthlySavings = $financialProfile ? (float) $financialProfile->available_monthly_savings : 0.0;
        
        // Projected Completion Date
        $projectedCompletionDate = null;
        if ($remainingAmount == 0) {
            $projectedCompletionDate = $today->format('Y-m-d');
        } elseif ($availableMonthlySavings > 0) {
            $monthsNeeded = ceil($remainingAmount / $availableMonthlySavings);
            $projectedCompletionDate = $today->copy()->addMonths((int)$monthsNeeded)->format('Y-m-d');
        }

        return [
            'current_savings' => round($currentSavings, 2),
            'remaining_amount' => round($remainingAmount, 2),
            'months_remaining' => (int) $monthsRemaining,
            'required_monthly_savings' => round($requiredMonthlySavings, 2),
            'projected_completion_date' => $projectedCompletionDate,
            'current_pace' => round($availableMonthlySavings, 2)
        ];
    }
}
