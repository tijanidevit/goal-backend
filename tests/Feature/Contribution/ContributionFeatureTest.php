<?php

namespace Tests\Feature\Contribution;

use App\Models\Goal;
use App\Models\GoalMilestone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContributionFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_contribution_and_milestone_is_achieved(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $goal = Goal::factory()->create([
            'user_id' => $user->id,
            'category' => 'Relocation',
            'name' => 'Move',
            'target_amount' => 10000,
            'target_date' => now()->addYear(),
        ]);

        $milestone = GoalMilestone::factory()->create([
            'goal_id' => $goal->id,
            'title' => '10% Achieved',
            'target_amount' => 1000,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson(route('goals.contributions.store', ['goal' => $goal->id]), [
            'amount' => 1500,
            'contribution_date' => now()->format('Y-m-d'),
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('goal_contributions', [
            'amount' => 1500,
        ]);

        $this->assertNotNull($milestone->fresh()->achieved_at);
    }
}
