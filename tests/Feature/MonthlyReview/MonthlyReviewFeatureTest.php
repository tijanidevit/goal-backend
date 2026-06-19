<?php

namespace Tests\Feature\MonthlyReview;

use App\Models\Goal;
use App\Models\GoalContribution;
use App\Models\GoalContributionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonthlyReviewFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_monthly_review(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $goal = Goal::create([
            'user_id' => $user->id,
            'category' => 'Relocation',
            'name' => 'Move',
            'target_amount' => 12000,
            'target_date' => now()->addMonths(12)->format('Y-m-d'),
        ]);

        GoalContributionPlan::create([
            'goal_id' => $goal->id,
            'amount' => 1000,
            'frequency' => 'monthly',
            'next_due_date' => now(),
            'active' => true,
        ]);

        GoalContribution::create([
            'goal_id' => $goal->id,
            'amount' => 1200,
            'contribution_date' => now()->startOfMonth(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson(route('monthly-review', ['month' => now()->format('m'), 'year' => now()->format('Y')]));

        $response->assertStatus(200)
                 ->assertJsonPath('goals_review.0.status', 'Met')
                 ->assertJsonPath('goals_review.0.planned_contribution', 1000)
                 ->assertJsonPath('goals_review.0.actual_contribution', 1200)
                 ->assertJsonPath('goals_review.0.difference', 200);
    }
}
