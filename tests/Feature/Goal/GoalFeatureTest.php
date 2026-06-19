<?php

namespace Tests\Feature\Goal;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_goal_and_milestones_are_generated(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson(route('goals.store'), [
            'category' => 'Relocation',
            'name' => 'Move to Canada',
            'target_amount' => 10000000,
            'target_date' => now()->addYear()->format('Y-m-d'),
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'id',
                     'name',
                     'milestones' => [
                         '*' => ['id', 'title', 'target_amount']
                     ]
                 ]);

        $this->assertDatabaseHas('goals', [
            'name' => 'Move to Canada',
        ]);

        $this->assertDatabaseCount('goal_milestones', 5); // 10, 25, 50, 75, 100
    }

    public function test_user_can_list_their_goals(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        Goal::create([
            'user_id' => $user->id,
            'category' => 'Test',
            'name' => 'My Goal',
            'target_amount' => 5000,
            'target_date' => now()->addMonth(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson(route('goals.index'));

        $response->assertStatus(200)
                 ->assertJsonCount(1);
    }
}
