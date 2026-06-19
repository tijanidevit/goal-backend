<?php

namespace Tests\Unit\Engine;

use App\Models\Goal;
use App\Models\GoalContributionPlan;
use App\Models\User;
use App\Services\ForecastEngine;
use App\Services\HealthEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_on_track_when_forecast_is_before_target()
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

        $engine = new HealthEngine(new ForecastEngine());
        $status = $engine->determineHealthStatus($goal);

        $this->assertEquals('On Track', $status);
    }

    public function test_it_returns_behind_when_forecast_is_slightly_late()
    {
        $user = User::factory()->create();
        $goal = Goal::create([
            'user_id' => $user->id,
            'category' => 'savings',
            'name' => 'Car',
            'target_amount' => 1200,
            'target_date' => now()->addMonths(10)->format('Y-m-d'), // Target is 10 months away
        ]);

        GoalContributionPlan::create([
            'goal_id' => $goal->id,
            'amount' => 100, // Will take 12 months (2 months late)
            'frequency' => 'monthly',
            'next_due_date' => now(),
            'active' => true,
        ]);

        $engine = new HealthEngine(new ForecastEngine());
        $status = $engine->determineHealthStatus($goal);

        $this->assertEquals('Behind', $status);
    }
}
