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
                     'success',
                     'message',
                     'data' => [
                         'id',
                         'name',
                         'milestones' => [
                             '*' => ['id', 'title', 'target_amount']
                         ]
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

        Goal::factory()->create([
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
                 ->assertJsonCount(1, 'data');
    }

    public function test_user_cannot_create_goal_with_past_date(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson(route('goals.store'), [
            'category' => 'Relocation',
            'name' => 'Past Goal',
            'target_amount' => 1000,
            'target_date' => now()->subDay()->format('Y-m-d'),
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['target_date']);
    }

    public function test_user_cannot_view_others_goal(): void
    {
        $owner = User::factory()->create();
        $goal = Goal::factory()->create([
            'user_id' => $owner->id,
            'category' => 'Test',
            'name' => 'Private Goal',
            'target_amount' => 5000,
            'target_date' => now()->addMonth(),
        ]);

        $otherUser = User::factory()->create();
        $token = $otherUser->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson(route('goals.show', $goal->id));

        $response->assertStatus(403);
    }
}
