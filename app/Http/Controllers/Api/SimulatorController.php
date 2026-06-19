<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Goal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SimulatorController extends Controller
{
    public function simulate(Request $request, Goal $goal): JsonResponse
    {
        if ($goal->user_id !== $request->user()->id) {
            return $this->unauthorizedResponse();
        }

        $request->validate([
            'proposed_monthly_contribution' => ['required', 'numeric', 'min:1'],
        ]);

        $proposedAmount = (float) $request->input('proposed_monthly_contribution');
        
        $currentSavings = $goal->contributions()->sum('amount');
        $remainingAmount = $goal->target_amount - $currentSavings;

        if ($remainingAmount <= 0) {
            return $this->successResponse('Goal is already achieved.', [
                'estimated_completion_date' => now()->format('Y-m-d'),
                'months_left' => 0,
            ]);
        }

        $monthsLeft = ceil($remainingAmount / $proposedAmount);
        $estimatedDate = now()->addMonths((int) $monthsLeft);

        return $this->successResponse('Simulation complete', [
            'proposed_monthly_contribution' => $proposedAmount,
            'estimated_completion_date' => $estimatedDate->format('Y-m-d'),
            'months_left' => $monthsLeft,
        ]);
    }
}
