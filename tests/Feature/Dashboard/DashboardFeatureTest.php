<?php

namespace Tests\Feature\Dashboard;

use App\Models\FinancialProfile;
use App\Models\Goal;
use App\Models\GoalContributionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_dashboard_with_financial_summary_and_enriched_goals(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        FinancialProfile::factory()->create([
            'user_id' => $user->id,
            'total_monthly_income' => 5000,
            'total_monthly_expenses' => 2000,
            'total_monthly_debt_repayment' => 500,
            'available_monthly_savings' => 2500,
        ]);

        $goal = Goal::factory()->create([
            'user_id' => $user->id,
            'category' => 'travel',
            'name' => 'Move',
            'target_amount' => 1000,
            'target_date' => now()->addMonths(10)->format('Y-m-d'),
        ]);

        GoalContributionPlan::factory()->create([
            'goal_id' => $goal->id,
            'amount' => 100,
            'frequency' => 'monthly',
            'next_due_date' => now(),
            'active' => true,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson(route('dashboard'));

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'user',
                         'financial_summary' => [
                             'total_income',
                             'total_expenses',
                             'total_debt_repayment',
                             'available_savings',
                         ],
                         'goals' => [
                             '*' => [
                                 'id',
                                 'name',
                                 'category',
                                 'target_amount',
                                 'target_date',
                                 'current_savings',
                                 'goal_health' => [
                                     'status',
                                     'label',
                                 ],
                                 'current_milestone',
                                 'next_milestone',
                                 'amount_remaining_to_next_milestone',
                                 'if_you_continue_like_this'
                             ]
                         ]
                     ]
                 ]);

        // Health engine should say 'On Track'.
        $this->assertEquals('On Track', $response->json('data.goals.0.goal_health.label'));
    }
}
