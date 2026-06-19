<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardResource;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        
        // Eager load necessary relationships to avoid N+1 queries
        $user->load(['financialProfile', 'goals.contributions']);
        
        $financialProfile = $user->financialProfile;
        $goals = $user->goals;

        $financialSummary = null;
        if ($financialProfile) {
            $financialSummary = [
                'total_income' => $financialProfile->total_monthly_income,
                'total_expenses' => $financialProfile->total_monthly_expenses,
                'total_debt_repayment' => $financialProfile->total_monthly_debt_repayment,
                'available_savings' => $financialProfile->available_monthly_savings,
            ];
        }

        $dashboardPayloads = $goals->map(function ($goal) use ($financialProfile) {
            return $this->dashboardService->generateGoalDashboard($goal, $financialProfile);
        });

        return response()->json([
            'user' => $user->only(['id', 'first_name', 'last_name', 'email']),
            'financial_summary' => $financialSummary,
            'goals' => DashboardResource::collection($dashboardPayloads),
        ]);
    }
}
