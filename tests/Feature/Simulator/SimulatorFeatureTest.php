<?php

namespace Tests\Feature\Simulator;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimulatorFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_simulate_goal_impact(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $goal = Goal::factory()->create([
            'user_id' => $user->id,
            'category' => 'Relocation',
            'name' => 'Move',
            'target_amount' => 12000,
            'target_date' => now()->addMonths(12)->format('Y-m-d'),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson(route('goals.simulate', ['goal' => $goal->id]), [
            'proposed_monthly_contribution' => 1000,
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'proposed_monthly_contribution' => 1000,
                     'months_left' => 12,
                 ]);
    }
}
