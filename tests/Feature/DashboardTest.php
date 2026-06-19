<?php

namespace Tests\Feature;

use App\Models\FinancialProfile;
use App\Models\Goal;
use App\Models\GoalContribution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_dashboard()
    {
        $response = $this->getJson('/api/dashboard');
        $response->assertStatus(401);
    }

    public function test_dashboard_endpoint_returns_combined_payload()
    {
        $user = User::factory()->create();
        
        $profile = FinancialProfile::factory()->create([
            'user_id' => $user->id,
            'total_monthly_income' => 5000,
            'total_monthly_expenses' => 2000,
            'total_monthly_debt_repayment' => 1000,
            // available automatically calculated as 2000
        ]);

        $goal = Goal::factory()->create([
            'user_id' => $user->id,
            'name' => 'Buy a House',
            'category' => 'savings',
            'target_amount' => 10000,
            'target_date' => now()->addMonths(5),
            'currency_code' => 'NGN',
            'is_primary' => false,
            'status' => 'active',
            'created_at' => now()->subMonths(1),
        ]);

        GoalContribution::factory()->create([
            'goal_id' => $goal->id,
            'amount' => 4000,
            'contribution_date' => now(),
        ]);

        $response = $this->actingAs($user)->getJson('/api/dashboard');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user',
                'financial_summary' => [
                    'total_income',
                    'total_expenses',
                    'total_debt_repayment',
                    'available_savings'
                ],
                'goals' => [
                    '*' => [
                        'id',
                        'is_primary',
                        'category',
                        'name',
                        'goal_name',
                        'target_amount',
                        'target_date',
                        'current_savings',
                        'remaining_amount',
                        'progress_percentage',
                        'available_monthly_savings',
                        'required_monthly_savings',
                        'projected_completion_date',
                        'goal_health' => [
                            'status',
                            'label'
                        ],
                        'current_milestone',
                        'next_milestone',
                        'amount_remaining_to_next_milestone',
                        'timeline' => [
                            'start_date',
                            'target_date',
                            'months_elapsed',
                            'total_months'
                        ],
                        'if_you_continue_like_this' => [
                            'current_pace',
                            'projected_completion_date',
                            'target_date',
                            'status'
                        ]
                    ]
                ]
            ]
        ]);

        $data = $response->json('data.goals.0');
        $this->assertEquals('Buy a House', $data['goal_name']);
        $this->assertEquals(10000, $data['target_amount']);
        $this->assertEquals(4000, $data['current_savings']);
        $this->assertEquals(6000, $data['remaining_amount']);
        $this->assertEquals(40, $data['progress_percentage']);
        $this->assertEquals(2000, $data['available_monthly_savings']);
        $this->assertEquals(1200, $data['required_monthly_savings']);
        $this->assertEquals('green', $data['goal_health']['status']);
    }
}
