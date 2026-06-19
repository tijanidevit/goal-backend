<?php

namespace Tests\Unit\Engine;

use App\Models\Goal;
use App\Models\GoalContributionPlan;
use App\Models\User;
use App\Services\ForecastEngine;
use App\Services\ProbabilityEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProbabilityEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_100_when_on_track()
    {
        $user = User::factory()->create();
        $goal = Goal::create([
            'user_id' => $user->id,
            'category' => 'savings',
            'name' => 'Car',
            'target_amount' => 1000,
            'target_date' => now()->addMonths(12)->format('Y-m-d'),
        ]);

        GoalContributionPlan::create([
            'goal_id' => $goal->id,
            'amount' => 100, // 10 months
            'frequency' => 'monthly',
            'next_due_date' => now(),
            'active' => true,
        ]);

        $engine = new ProbabilityEngine(new ForecastEngine());
        $probability = $engine->calculateProbability($goal);

        $this->assertEquals(100, $probability);
    }

    public function test_it_returns_lower_probability_when_late()
    {
        $user = User::factory()->create();
        
        // Goal created 2 months ago, target in 10 months. Total 12 months.
        $goal = Goal::create([
            'user_id' => $user->id,
            'category' => 'savings',
            'name' => 'Car',
            'target_amount' => 1200,
            'target_date' => now()->addMonths(10)->format('Y-m-d'),
            'created_at' => now()->subMonths(2),
        ]);

        GoalContributionPlan::create([
            'goal_id' => $goal->id,
            'amount' => 100, // Will take 12 months (2 months late relative to target)
            'frequency' => 'monthly',
            'next_due_date' => now(),
            'active' => true,
        ]);

        $engine = new ProbabilityEngine(new ForecastEngine());
        $probability = $engine->calculateProbability($goal);

        // Target Date is 10 months away.
        // Estimated Date is 12 months away. (2 months late)
        // Since Laravel auto-sets created_at to now() in create(), total planned months = 10 months.
        // Lateness ratio = 2 / 10 = 20%
        // Probability = 100 - 20 = 80
        
        $this->assertEquals(80, $probability);
    }
}
