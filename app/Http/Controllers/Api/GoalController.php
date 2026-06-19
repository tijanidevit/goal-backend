<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Goal\StoreGoalRequest;
use App\Http\Requests\Goal\UpdateGoalRequest;
use App\Models\Goal;
use App\Models\GoalMilestone;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $goals = $request->user()->goals()->with(['contributions', 'milestones'])->get();
        return $this->successResponse('Goals retrieved successfully', $goals);
    }

    public function store(StoreGoalRequest $request): JsonResponse
    {
        $validated = $request->validated();
        if ($request->user()->goals()->count() === 0) {
            $validated['is_primary'] = true;
        }

        $goal = $request->user()->goals()->create($validated);

        $this->generateMilestones($goal);

        return $this->createdResponse('Goal created successfully', $goal->load('milestones'));
    }

    public function show(Request $request, Goal $goal, DashboardService $dashboardService): JsonResponse
    {
        if ($goal->user_id !== $request->user()->id) {
            return $this->unauthorizedResponse();
        }

        $goal->load(['contributions', 'milestones', 'contributionPlans']);
        
        $financialProfile = $request->user()->financialProfile;
        $insights = $dashboardService->generateGoalDashboard($goal, $financialProfile);

        $goalData = array_merge($goal->toArray(), [
            'goal_health' => $insights['goal_health'],
            'current_milestone' => $insights['current_milestone'],
            'next_milestone' => $insights['next_milestone'],
            'amount_remaining_to_next_milestone' => $insights['amount_remaining_to_next_milestone'],
            'if_you_continue_like_this' => $insights['if_you_continue_like_this'],
            'timeline' => $insights['timeline']
        ]);

        return $this->successResponse('Goal retrieved successfully', $goalData);
    }

    public function update(UpdateGoalRequest $request, Goal $goal): JsonResponse
    {
        if ($goal->user_id !== $request->user()->id) {
            return $this->unauthorizedResponse();
        }

        $goal->update($request->validated());

        return $this->successResponse('Goal updated successfully', $goal);
    }

    public function destroy(Request $request, Goal $goal): JsonResponse
    {
        if ($goal->user_id !== $request->user()->id) {
            return $this->unauthorizedResponse();
        }

        $goal->delete();

        return $this->successMessageResponse('Goal deleted successfully');
    }

    public function setPrimary(Request $request, Goal $goal): JsonResponse
    {
        if ($goal->user_id !== $request->user()->id) {
            return $this->unauthorizedResponse();
        }

        $request->user()->goals()->update(['is_primary' => false]);
        $goal->update(['is_primary' => true]);

        return $this->successResponse('Goal set as primary successfully', $goal->load(['contributions', 'milestones', 'contributionPlans']));
    }

    private function generateMilestones(Goal $goal): void
    {
        $percentages = [10, 25, 50, 75, 100];
        
        foreach ($percentages as $percentage) {
            $targetAmount = $goal->target_amount * ($percentage / 100);
            
            GoalMilestone::create([
                'goal_id' => $goal->id,
                'title' => "{$percentage}% Achieved",
                'target_amount' => $targetAmount,
            ]);
        }
    }
}
