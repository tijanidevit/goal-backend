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
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($goal->contributions);
    }

    public function store(StoreContributionRequest $request, Goal $goal): JsonResponse
    {
        if ($goal->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();
        $newAmount = $validated['amount'];
        $currentSavings = $goal->contributions()->sum('amount');
        
        if ($currentSavings + $newAmount > $goal->target_amount) {
            $remaining = max(0, $goal->target_amount - $currentSavings);
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'amount' => ["This contribution exceeds your remaining target amount of {$remaining}."]
                ]
            ], 422);
        }

        $contribution = $goal->contributions()->create($validated);

        $this->checkMilestones($goal);

        return response()->json($contribution, 201);
    }

    public function update(UpdateContributionRequest $request, GoalContribution $contribution): JsonResponse
    {
        $goal = $contribution->goal;
        
        if ($goal->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $contribution->update($request->validated());

        $this->checkMilestones($goal);

        return response()->json($contribution);
    }

    public function destroy(GoalContribution $contribution): JsonResponse
    {
        $goal = $contribution->goal;
        
        if ($goal->user_id !== request()->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $contribution->delete();

        return response()->json(null, 204);
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
