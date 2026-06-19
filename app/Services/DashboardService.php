<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\FinancialProfile;
use Carbon\Carbon;

class DashboardService
{
    protected ForecastEngineService $forecastEngine;
    protected GoalHealthService $healthEngine;
    protected MilestoneEngineService $milestoneEngine;

    public function __construct(
        ForecastEngineService $forecastEngine,
        GoalHealthService $healthEngine,
        MilestoneEngineService $milestoneEngine
    ) {
        $this->forecastEngine = $forecastEngine;
        $this->healthEngine = $healthEngine;
        $this->milestoneEngine = $milestoneEngine;
    }

    /**
     * Generate the combined dashboard payload for a given goal.
     * 
     * @param Goal $goal
     * @param FinancialProfile|null $financialProfile
     * @return array
     */
    public function generateGoalDashboard(Goal $goal, ?FinancialProfile $financialProfile): array
    {
        $forecast = $this->forecastEngine->forecast($goal, $financialProfile);
        $health = $this->healthEngine->calculateHealth($forecast['required_monthly_savings'], $forecast['current_pace']);
        $milestones = $this->milestoneEngine->calculateMilestones($forecast['current_savings'], (float) $goal->target_amount);

        // Timeline calculations
        $startDate = $goal->created_at ? $goal->created_at->startOfDay() : now()->startOfDay();
        $targetDate = Carbon::parse($goal->target_date)->startOfDay();
        $today = now()->startOfDay();

        $totalMonths = $startDate->diffInMonths($targetDate, false);
        $totalMonths = max(0, $totalMonths);
        
        $monthsElapsed = $startDate->diffInMonths($today, false);
        $monthsElapsed = max(0, $monthsElapsed);

        return [
            'id' => $goal->id,
            'is_primary' => (bool)$goal->is_primary,
            'category' => $goal->category,
            'name' => $goal->name,
            'goal_name' => $goal->name,
            'target_amount' => (float) $goal->target_amount,
            'target_date' => $goal->target_date->format('Y-m-d'),
            
            'current_savings' => $forecast['current_savings'],
            'remaining_amount' => $forecast['remaining_amount'],
            
            'progress_percentage' => $milestones['current_progress_percentage'],
            
            'available_monthly_savings' => (float) ($financialProfile ? $financialProfile->available_monthly_savings : 0.0),
            'required_monthly_savings' => $forecast['required_monthly_savings'],
            'projected_completion_date' => $forecast['projected_completion_date'],
            
            'goal_health' => $health,
            
            'current_milestone' => $milestones['current_milestone'],
            'next_milestone' => $milestones['next_milestone'],
            'amount_remaining_to_next_milestone' => $milestones['amount_remaining_to_next_milestone'],
            
            'timeline' => [
                'start_date' => $startDate->format('Y-m-d'),
                'target_date' => $targetDate->format('Y-m-d'),
                'months_elapsed' => (int) $monthsElapsed,
                'total_months' => (int) $totalMonths,
            ],
            
            'if_you_continue_like_this' => [
                'current_pace' => $forecast['current_pace'],
                'projected_completion_date' => $forecast['projected_completion_date'],
                'target_date' => $targetDate->format('Y-m-d'),
                'status' => $health['label']
            ]
        ];
    }
}
