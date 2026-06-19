<?php

namespace App\Http\Controllers\Api;

use App\Enums\EnumContributionFrequency;
use App\Enums\EnumMonthlyReviewStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MonthlyReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $month = $request->query('month', now()->format('m'));
        $year = $request->query('year', now()->format('Y'));

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $goals = $user->goals()->with(['contributions' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('contribution_date', [$startDate, $endDate]);
        }, 'contributionPlans' => function($query) {
            $query->where('active', true);
        }])->get();

        $review = $goals->map(function ($goal) {
            $totalContributedThisMonth = $goal->contributions->sum('amount');
            
            $plannedThisMonth = 0;
            foreach ($goal->contributionPlans as $plan) {
                if ($plan->frequency === EnumContributionFrequency::MONTHLY) {
                    $plannedThisMonth += $plan->amount;
                } elseif ($plan->frequency === EnumContributionFrequency::WEEKLY) {
                    $plannedThisMonth += $plan->amount * 4.33; 
                }
            }

            return [
                'goal_id' => $goal->id,
                'goal_name' => $goal->name,
                'planned_contribution' => round($plannedThisMonth, 2),
                'actual_contribution' => round($totalContributedThisMonth, 2),
                'difference' => round($totalContributedThisMonth - $plannedThisMonth, 2),
                'status' => $totalContributedThisMonth >= $plannedThisMonth ? EnumMonthlyReviewStatus::MET->value : EnumMonthlyReviewStatus::MISSED->value,
            ];
        });

        return $this->successResponse('Monthly review retrieved successfully', [
            'month' => $month,
            'year' => $year,
            'goals_review' => $review,
        ]);
    }
}
