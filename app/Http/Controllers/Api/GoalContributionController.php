<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contribution\StoreContributionRequest;
use App\Http\Requests\Contribution\UpdateContributionRequest;
use App\Models\Goal;
use App\Models\GoalContribution;
use Illuminate\Http\JsonResponse;

class GoalContributionController extends Controller
{
    public function index(Goal $goal): JsonResponse
    {
        if ($goal->user_id !== request()->user()->id) {
            return $this->unauthorizedResponse();
        }

        return $this->successResponse('Contributions retrieved successfully', $goal->contributions);
    }

    public function store(StoreContributionRequest $request, Goal $goal): JsonResponse
    {
        if ($goal->user_id !== $request->user()->id) {
            return $this->unauthorizedResponse();
        }

        $validated = $request->validated();
        $newAmount = $validated['amount'];
        $currentSavings = $goal->contributions()->sum('amount');
        
        if ($currentSavings + $newAmount > $goal->target_amount) {
            $remaining = max(0, $goal->target_amount - $currentSavings);
            return $this->errorResponse('The given data was invalid.', [
                'amount' => ["This contribution exceeds your remaining target amount of {$remaining}."]
            ], 422);
        }

        $contribution = $goal->contributions()->create($validated);

        $this->checkMilestones($goal);

        return $this->createdResponse('Contribution created successfully', $contribution);
    }

    public function update(UpdateContributionRequest $request, GoalContribution $contribution): JsonResponse
    {
        $goal = $contribution->goal;
        
        if ($goal->user_id !== $request->user()->id) {
            return $this->unauthorizedResponse();
        }

        $contribution->update($request->validated());

        $this->checkMilestones($goal);

        return $this->successResponse('Contribution updated successfully', $contribution);
    }

    public function destroy(GoalContribution $contribution): JsonResponse
    {
        $goal = $contribution->goal;
        
        if ($goal->user_id !== request()->user()->id) {
            return $this->unauthorizedResponse();
        }

        $contribution->delete();

        return $this->successMessageResponse('Contribution deleted successfully');
    }

    private function checkMilestones(Goal $goal): void
    {
        $currentSavings = $goal->contributions()->sum('amount');
        
        $unachievedMilestones = $goal->milestones()->whereNull('achieved_at')->get();
        
        foreach ($unachievedMilestones as $milestone) {
            if ($currentSavings >= $milestone->target_amount) {
                $milestone->update(['achieved_at' => now()]);
            }
        }
    }
}
