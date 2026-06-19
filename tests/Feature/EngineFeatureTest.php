<?php

namespace Tests\Feature;

use App\Models\FinancialProfile;
use App\Models\Goal;
use App\Models\GoalContribution;
use App\Models\User;
use App\Services\ForecastEngineService;
use App\Services\GoalHealthService;
use App\Services\MilestoneEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EngineFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_forecast_engine_with_contributions_and_financial_profile()
    {
        $user = User::factory()->create();
        
        $profile = FinancialProfile::factory()->create([
            'user_id' => $user->id,
            'total_monthly_income' => 5000,
            'total_monthly_expenses' => 2000,
            'total_monthly_debt_repayment' => 1000,
            // available = 2000
        ]);

        $goal = Goal::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Goal',
            'category' => 'savings',
            'target_amount' => 10000,
            'target_date' => now()->addMonths(5),
            'currency_code' => 'NGN',
            'is_primary' => false,
            'status' => 'active'
        ]);

        GoalContribution::factory()->create([
            'goal_id' => $goal->id,
            'amount' => 4000,
            'contribution_date' => now(),
        ]);

        $service = new ForecastEngineService();
        $result = $service->forecast($goal, $profile);

        $this->assertEquals(4000, $result['current_savings']);
        $this->assertEquals(6000, $result['remaining_amount']);
        $this->assertEquals(5, $result['months_remaining']);
        // 6000 / 5 = 1200
        $this->assertEquals(1200, $result['required_monthly_savings']);
        $this->assertEquals(2000, $result['current_pace']);
        
        // 6000 / 2000 = 3 months needed
        $expectedDate = now()->startOfDay()->addMonths(3)->format('Y-m-d');
        $this->assertEquals($expectedDate, $result['projected_completion_date']);
    }

    public function test_forecast_engine_target_date_already_passed()
    {
        $user = User::factory()->create();
        $goal = Goal::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Goal',
            'category' => 'savings',
            'target_amount' => 10000,
            'target_date' => now()->subMonths(1), // passed
            'currency_code' => 'NGN',
            'is_primary' => false,
            'status' => 'active'
        ]);

        $service = new ForecastEngineService();
        $result = $service->forecast($goal, null);

        $this->assertEquals(0, $result['months_remaining']);
        $this->assertEquals(10000, $result['required_monthly_savings']);
    }

    public function test_forecast_engine_zero_contributions()
    {
        $user = User::factory()->create();
        $goal = Goal::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Goal',
            'category' => 'savings',
            'target_amount' => 10000,
            'target_date' => now()->addMonths(10),
            'currency_code' => 'NGN',
            'is_primary' => false,
            'status' => 'active'
        ]);

        $service = new ForecastEngineService();
        $result = $service->forecast($goal, null);

        $this->assertEquals(0, $result['current_savings']);
        $this->assertEquals(10000, $result['remaining_amount']);
        $this->assertEquals(1000, $result['required_monthly_savings']);
    }

    public function test_forecast_engine_goal_already_achieved()
    {
        $user = User::factory()->create();
        $goal = Goal::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Goal',
            'category' => 'savings',
            'target_amount' => 10000,
            'target_date' => now()->addMonths(10),
            'currency_code' => 'NGN',
            'is_primary' => false,
            'status' => 'active'
        ]);

        GoalContribution::factory()->create([
            'goal_id' => $goal->id,
            'amount' => 12000, // exceeded
            'contribution_date' => now(),
        ]);

        $service = new ForecastEngineService();
        $result = $service->forecast($goal, null);

        $this->assertEquals(12000, $result['current_savings']);
        $this->assertEquals(0, $result['remaining_amount']);
        $this->assertEquals(0, $result['required_monthly_savings']);
        $this->assertEquals(now()->startOfDay()->format('Y-m-d'), $result['projected_completion_date']);
    }

    public function test_health_engine()
    {
        $service = new GoalHealthService();

        // GREEN
        $result = $service->calculateHealth(1000, 1100);
        $this->assertEquals('green', $result['status']);

        // YELLOW
        $result = $service->calculateHealth(1000, 950);
        $this->assertEquals('yellow', $result['status']);

        // RED
        $result = $service->calculateHealth(1000, 800);
        $this->assertEquals('red', $result['status']);
        
        // Achieved / No savings required
        $result = $service->calculateHealth(0, 500);
        $this->assertEquals('green', $result['status']);
    }

    public function test_milestone_engine()
    {
        $service = new MilestoneEngineService();

        $result = $service->calculateMilestones(4300, 10000);
        
        $this->assertEquals(43, $result['current_progress_percentage']);
        $this->assertEquals(25, $result['current_milestone']);
        $this->assertEquals(50, $result['next_milestone']);
        $this->assertEquals(700, $result['amount_remaining_to_next_milestone']);
        
        $resultAchieved = $service->calculateMilestones(10000, 10000);
        $this->assertEquals(100, $resultAchieved['current_progress_percentage']);
        $this->assertEquals(100, $resultAchieved['current_milestone']);
        $this->assertNull($resultAchieved['next_milestone']);
        $this->assertEquals(0, $resultAchieved['amount_remaining_to_next_milestone']);
    }

    public function test_forecast_engine_negative_available_savings()
    {
        $user = User::factory()->create();
        
        $profile = FinancialProfile::factory()->create([
            'user_id' => $user->id,
            'total_monthly_income' => 2000,
            'total_monthly_expenses' => 3000,
            'total_monthly_debt_repayment' => 500,
        ]);

        $goal = Goal::factory()->create([
            'user_id' => $user->id,
            'name' => 'Test Goal',
            'category' => 'savings',
            'target_amount' => 10000,
            'target_date' => now()->addMonths(5),
            'currency_code' => 'NGN',
            'is_primary' => false,
            'status' => 'active'
        ]);

        $service = new ForecastEngineService();
        $result = $service->forecast($goal, $profile);

        $this->assertNull($result['projected_completion_date']);
        $this->assertEquals(0, $result['current_pace']);
    }

    public function test_health_engine_negative_pace()
    {
        $service = new GoalHealthService();

        $result = $service->calculateHealth(1000, -500);
        $this->assertEquals('red', $result['status']);
    }

    public function test_milestone_engine_zero_target()
    {
        $service = new MilestoneEngineService();

        $result = $service->calculateMilestones(1000, 0);
        
        $this->assertEquals(100, $result['current_progress_percentage']);
        $this->assertEquals(100, $result['current_milestone']);
        $this->assertNull($result['next_milestone']);
        $this->assertEquals(0, $result['amount_remaining_to_next_milestone']);
    }

    public function test_milestone_engine_negative_savings()
    {
        $service = new MilestoneEngineService();

        $result = $service->calculateMilestones(-500, 10000);
        
        $this->assertEquals(0, $result['current_progress_percentage']);
        $this->assertEquals(0, $result['current_milestone']);
        $this->assertEquals(10, $result['next_milestone']);
        $this->assertEquals(1500, $result['amount_remaining_to_next_milestone']);
    }
}
