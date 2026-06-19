<?php

namespace Tests\Unit\Engine;

use App\Models\Goal;
use App\Models\GoalContributionPlan;
use App\Models\User;
use App\Services\ForecastEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForecastEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_forecasts_completion_date_correctly()
    {
        $user = User::factory()->create();
        $goal = Goal::create([
            'user_id' => $user->id,
            'category' => 'savings',
            'name' => 'Car',
            'target_amount' => 1000,
            'target_date' => now()->addMonths(10)->format('Y-m-d'),
        ]);

        GoalContributionPlan::create([
            'goal_id' => $goal->id,
            'amount' => 100,
            'frequency' => 'monthly',
            'next_due_date' => now(),
            'active' => true,
        ]);

        $engine = new ForecastEngine();
        $estimatedDate = $engine->calculateEstimatedCompletionDate($goal);

        $expectedDate = now()->addMonths(10)->format('Y-m');
        $this->assertEquals($expectedDate, $estimatedDate->format('Y-m'));
    }

    public function test_it_returns_null_when_no_plan()
    {
        $user = User::factory()->create();
        $goal = Goal::create([
            'user_id' => $user->id,
            'category' => 'savings',
            'name' => 'Car',
            'target_amount' => 1000,
            'target_date' => now()->addMonths(10)->format('Y-m-d'),
        ]);

        $engine = new ForecastEngine();
        $estimatedDate = $engine->calculateEstimatedCompletionDate($goal);

        $this->assertNull($estimatedDate);
    }
}
